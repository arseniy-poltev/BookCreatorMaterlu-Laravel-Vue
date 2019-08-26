import time
import base64
import codecs
from pyquery import PyQuery as pq
from pdfrw import PdfReader, PdfWriter
from svglib.svglib import svg2rlg
import json
import os
import shutil
import io
import re
import sys
import string
import re
import lxml.html
from shutil import copyfile
from pprint import pprint
from reportlab.graphics import renderPDF, renderPM
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
import reportlab.rl_config
from PIL import Image
from PyPDF2 import PdfFileWriter, PdfFileReader
from selectolax.parser import HTMLParser
reportlab.rl_config.warnOnMissingFontGlyphs = 0


# basic configuration and global variables

layerParamFN = "layer.json"
configFN = "param.json"
sourceDir = "origin"
targetDir = "target"
applyDir = "apply"
pngDir = "png"
outputFile = "final_result.pdf"

dpi_web = 72
dpi_print = 300
pdf_inchwidth = 22.6/2.54
pdf_inchheight = 22.6/2.54

fontFile = ""

config = {}

pages = []

characters = []


# main functions


def read_json_param(bookNumber, filename):
    global config
    global sourceDir
    global pngDir
    global outputFile
    global pages
    global applyDir
    global targetDir
    global fontFile
    global characters

    path = os.path.dirname(os.path.realpath(__file__)) + os.sep + bookNumber
    fontFile = os.path.dirname(os.path.realpath(
        __file__)) + os.sep + "futura.ttf"

    filename = path + os.sep + filename
    paramJsonPath = path + os.sep + layerParamFN

    with io.open(file=filename, encoding="utf-8") as f:
        config = json.load(f)

    # if os.path.exists(paramJsonPath):
    #     with open(paramJsonPath) as pf:
    #         characters = json.load(pf)

    sourceDir = config["source_web_dir"]
    if config["quality"] == "print":
        sourceDir = config["source_print_dir"]
    pngDir = config["png_dir"]
    outputFile = config["output_file"]

    # added by Xiuna

    if 'pages' in config:
        pages = config['pages']

        sourceDir = path + os.sep + sourceDir
        pngDir = path + os.sep + pngDir
        outputFile = path + os.sep + outputFile
        applyDir = path + os.sep + applyDir
        targetDir = path + os.sep + targetDir

    # pprint(config)


def clear_all(dir):
    for the_file in os.listdir(dir):
        file_path = os.path.join(dir, the_file)
        os.unlink(file_path)


def nochar_pos(str):
    for idx, val in enumerate(str):
        if val.isalpha() is True:
            continue
        return idx


def solo_letras(str):
    # regex = re.compile("/[^a-zA-Z].*/", re.IGNORECASE | re.MULTILINE)
    # regex = '[^a-zA-Z].*(?gm)'
    # subst = ''
    # return str.replace(regex, subst)
    pos = nochar_pos(str)
    return str[:pos]


def apply_svg():
    global config

    index = 0

    for subdir, dirs, files in os.walk(sourceDir):
        # for file in files:
        for file in pages:
            print(file)
            # if(file.startswith(config["story"])) != True:
            #     continue

            if file.endswith("svg") != True:
                shutil.copyfile(subdir+os.sep+file, applyDir+os.sep+file)
                continue

            print("processing file : " + str(index))
            index = index + 1

            filepath = subdir + os.sep + file

            apply_path = applyDir + os.sep + file.replace("svg", "svg")

            # copyfile(filepath, apply_path)
            print(filepath)

            div_content = open(filepath, "rb").read()
            print(type(div_content))

            div_file = pq(div_content)

            # re-quality base64 image
            img_content = div_file.find("#fondo image")
            if img_content.length > 0 and config["quality"] != "print":
                strOne = pq(img_content).attr("xlink:href").partition(",")[2]
                pad = len(strOne) % 4
                strOne += str(b"=" * pad)
                print(len(strOne))
                imgdata = base64.b64decode(strOne)
                # I assume you have a way of picking unique filenames
                filename = 'target//' + file + '.jpg'
                with open(filename, 'wb') as f:
                    f.write(imgdata)

                with open(filepath, "rb") as image_file:
                    print(len(base64.b64encode(image_file.read())))

            # name replace
            font_style = config['font_style']

            tspan_elements = div_file("[id^=txt]")
            for tspan in tspan_elements:
                txt = pq(tspan).text()

                for model in config['characters']:
                    key = model['name'][0]
                    value = model['name'][1]
                    if key != None and key != '':
                        txt = txt.replace(key, value)

                if font_style == "uppercase":
                    font_size = pq(tspan).attr("font-size")
                    size = int(re.search(r'\d+', font_size).group())
                    if size > 0:
                        pq(tspan).attr("font-size", str(size - 4) + "px")
                    txt = txt.upper()
                pq(tspan).text(txt)
            # print("text:starting---------------------")
            # tspan_elements = div_file.find("tspan")
            # for tspan in tspan_elements:
            #     txt = pq(tspan).text()
            #     print(txt)
            #     # key = config['name'][0]
            #     key = "De"
            #     value = config['name'][1]
            #     if key != None and key != '':
            #         txt = txt.replace(key, value)
            #     print("tspan text next: " + txt)
            #     pq(tspan).text(txt)
            # print("text:ended---------------------")

            #gender = config["gender"]

            #gender = {True: "chico", False: "chica"}[gender == "boy"]

            # hide/show
            character_cnt = len(config['characters'])
            for model in config['characters']:
                identifier = ''
                if character_cnt > 1:
                    identifier = str(model["id"])
                gender = model["gender"]
                character = div_file.find(
                    "[id^='" + gender + identifier + "']")
                if len(character) == 0:
                    identifier = ''

                div_file.find("[id^='chica" + identifier + "']").css(
                    "display", "none").attr("display", "none")
                div_file.find("[id^='chico" + identifier + "']").css(
                    "display", "none").attr("display", "none")
                character = div_file.find(
                    "[id^='" + gender + identifier + "']")

                character.css("display",
                              "block").attr("display", "block")
                character.css("display",
                              "block").attr("display", "block")

                # # color
                # for key, val in model["color"].items():
                #     for i, v in enumerate(val):
                #         if i == 0 and val[i] != "":
                #             character.find("[id^=" + key + "]").css("fill",
                #                                                     "#" + v).attr("fill", "#" + v)
                #         if i == 1 and val[i] != "":
                #             character.find(
                #                 "[id^=" + key + "]").css("stroke", "#" + v).attr("stroke", "#" + v)

                # # # elements show
                # for key, val in model["show"].items():
                #     if len(val) == 0:
                #         continue
                #     z = solo_letras(val[0])
                #     character.find("[id^='" + key + "']").hide()
                #     # character.find("[id^='" + z + "']").show()

                #     character.find("[id^='" + z+"']").css("display",
                #                                           "none").attr("display", "none")
                #     show_ele = character.find("[id^='"+val[0]+"']:first")
                #     show_ele.css("display", "block").attr("display", "block")
                #     show_ele.children("*").css("display",
                #                                "block").attr("display", "block")
                #     show_ele.parents().css("display", "block").attr("display", "block")
                for key, val in model["layers"].items():
                    if val["show"] == "":
                        color_layer = character.find(
                            "[id^=" + val["color"]["target"] + "]")
                        if val["color"]["fill"] != "":
                            color_layer.css(
                                "fill", "#" + val["color"]["fill"]).attr("fill", "#" + val["color"]["fill"])
                        if val["color"]["stroke"] != "":
                            color_layer.css(
                                "stroke", "#" + val["color"]["stroke"]).attr("stroke", "#" + val["color"]["stroke"])
                        continue

                    z = solo_letras(val["show"])
                    character.find("[id^='" + z+"']").css("display",
                                                          "none").attr("display", "none")
                    show_ele = pq(character.find(
                        "[id^='"+val["show"]+"']:first"))
                    show_ele.css("display", "block").attr("display", "block")
                    show_ele.children("*").css("display",
                                               "block").attr("display", "block")
                    show_ele.parents().css("display", "block").attr("display", "block")
                    if val['color'] == "":
                        continue

                    if val["color"]["fill"] != "":
                        show_ele.children("[id^=color]").css(
                            "fill", "#" + val["color"]["fill"]).attr("fill", "#" + val["color"]["fill"])

                    if val["color"]["stroke"] != "":
                        show_ele.children("[id^=color]").css(
                            "stroke", "#" + val["color"]["stroke"]).attr("stroke", "#" + val["color"]["stroke"])

                    if show_ele.attr('fill'):
                        show_ele.css(
                            "fill", "#" + val["color"]["fill"]).attr("fill", "#" + val["color"]["fill"])
                    if show_ele.attr('stroke'):
                        show_ele.css("stroke", "#" + val["color"]
                                     ["stroke"]).attr("stroke", "#" + val["color"]["stroke"])

            # remove garbage

            div_file.find("[display=none]").remove()

            # writing result file
            f = codecs.open(apply_path, "w", 'utf8')
            f.write('<?xml version="1.0" encoding="utf-8"?>')

            contents = div_file.html().replace("<body>", "").replace(
                "</body>", "").replace("viewbox=", "viewBox=")
            contents = contents.replace(
                "'Futura-Medium'", "Futura").replace(" opacity=", " fill-opacity=")

            # for replace in config["name"]:
            #     for key, val in replace.items():
            #         contents = contents.replace(key, val)

            if "</svg>" not in contents:
                contents = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 652 652" enable-background="new 0 0 652 652" xml:space="preserve">' + contents + "</svg>"

            f.write(contents)


def svg_2_pdf():
    global config
    global dpi_web
    global dpi_print
    global pdf_inchheight
    global pdf_inchwidth

    dpi_final = dpi_web
    if config["quality"] == "print":
        dpi_final = dpi_print

    pdfmetrics.registerFont(TTFont("Futura", fontFile))

    for subdir, dirs, files in os.walk(applyDir):
        # sort_files(files)
        # for file in files:
        for file in pages:
            filepath = subdir + os.sep + file
            pdf_temp_path = targetDir + os.sep + \
                "temp_" + file.replace("svg", "pdf")
            pdf_path = targetDir + os.sep + file.replace("svg", "pdf")
            png_path = pngDir + os.sep + file.replace("svg", "png")

            if filepath.endswith(".svg"):
                drawing = svg2rlg(filepath)
                renderPM.drawToFile(drawing, png_path, "PNG")
                pngfile = Image.open(png_path)
                pngfile = pngfile.crop(
                    (0, 0, pdf_inchwidth*dpi_final, pdf_inchheight*dpi_final))
                pngfile.save(png_path)
                print("png generated : " + png_path)

                if config["quality"] == "web":
                    pngfile.save(pdf_path, dpi=(dpi_final, dpi_final))
                    print("pdf generated : " + pdf_path)
                else:
                    renderPDF.drawToFile(drawing, pdf_temp_path)
                    with open(pdf_temp_path, "rb") as in_f:
                        width = pdf_inchwidth*dpi_final
                        height = pdf_inchheight*dpi_final
                        delta = 0
                        input1 = PdfFileReader(in_f)
                        output = PdfFileWriter()

                        page = input1.getPage(0)
                        page.trimBox.lowerLeft = (delta, delta)
                        page.trimBox.upperRight = (width, height)
                        page.cropBox.lowerLeft = (delta, delta)
                        page.cropBox.upperRight = (width, height)
                        output.addPage(page)

                        with open(pdf_path, "wb") as out_f:
                            output.write(out_f)
                    os.unlink(pdf_temp_path)

                    print("pdf generated : " + pdf_path)


def get_file_no(file):
    nostr = file[2:-5]
    if is_number(nostr):
        return int(nostr)
    return 10000


def is_number(s):
    try:
        float(s)
        return True
    except ValueError:
        return False


def exchange(a, i, j):
    temp = a[i]
    a[i] = a[j]
    a[j] = temp


def sort_files(files):
    n = len(files)
    for i in range(n-1):
        for j in range(i+1, n):
            if get_file_no(files[i]) > get_file_no(files[j]):
                exchange(files, i, j)


def generate_pdf():
    print("generating final pdf...")

    writer = PdfWriter()

    for subdir, dirs, files in os.walk(targetDir):
        # sort_files(files)
        # for file in files:
        for file in pages:
            print(file)
            if file.endswith("svg") != True:
                continue
            file = file.replace("svg", "pdf")
            filepath = subdir + os.sep + file
            if filepath.endswith(".pdf"):
                writer.addpages(PdfReader(filepath).pages)

    # writer.write(config["story"] + "_" + config["lang"] + "_" + config["gender"] + "_WEB" + ".pdf")
    writer.write(outputFile)

    print("done!")

# ----------------------------------------------------------------------


if __name__ == "__main__":
    if len(sys.argv) == 1:
        print("parameter error!")
        exit()

    read_json_param(sys.argv[1], configFN)
    if not os.path.exists(sourceDir):
        print("source directory doesn't exist!")
        exit()

    if not os.path.exists(applyDir):
        os.makedirs(applyDir)
    if not os.path.exists(targetDir):
        os.makedirs(targetDir)
    if not os.path.exists(pngDir):
        os.makedirs(pngDir)

    # clear_all(applyDir)
    # clear_all(targetDir)
    # clear_all(pngDir)
    start_time = time.time()
    apply_svg()
    print("--- Total %s seconds ---" % (time.time() - start_time))
    # svg_2_pdf()
    # generate_pdf()
