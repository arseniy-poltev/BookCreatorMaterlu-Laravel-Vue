import sys
import os
import sys
import os
import json
import lxml.html
from pyquery import PyQuery as pq
from lxml import etree
from wand.image import Image
from wand.api import library
from wand.color import Color
import time

json_layer_path = 'layer_json'


def nochar_pos(str):
    for idx, val in enumerate(str):
        if val.isalpha() is True:
            continue
        return idx


def solo_letras(str):
    pos = nochar_pos(str)
    return str[:pos]


def svg2png(svg_content, output_path):
    with Image() as img:
        with Color('transparent') as bg_color:
            library.MagickSetBackgroundColor(img.wand, bg_color.resource)
        img.read(blob=svg_content)
        # img.resize(500, 500)
        dest_img = img.make_blob('png')
        with open(output_path, 'wb') as out:
            out.write(dest_img)


def apply_svg(book_id, character_id, input_path):
    div_content = open(input_path, "rb").read()
    div_file = pq(div_content)

    path = os.path.dirname(os.path.realpath(__file__)) + \
        os.sep + book_id + os.sep + json_layer_path
    json_path = path + os.sep + character_id + "_param.json"

    base_path = path + os.sep + character_id
    dir_path = "/uploads/book" + os.sep + book_id + \
        os.sep + json_layer_path + os.sep + character_id

    # make dir
    if not os.path.exists(base_path):
        os.makedirs(base_path)

    config = {}
    with open(json_path) as f:
        config = json.load(f)
    gender = config["gender"]
    # hide/show
    div_file.find("[id^=chico]").css(
        "display", "none").attr("display", "none")
    div_file.find("[id^=chica]").css(
        "display", "none").attr("display", "none")
    character = div_file.find("[id^=" + gender + "]")
    character.css("display", "block").attr("display", "block")
    character.css("display", "block").attr("display", "block")
    # color
    # for key, val in config["color"].items():
    #     layer = character.find("[id^=" + key + "]")
    #     for i, v in enumerate(val):
    #         if layer.attr('fill') == "none" and val[i] != "":
    #             layer.css("stroke", "#" + v).attr("stroke", "#" + v)
    #         else:
    #             if i == 0 and val[i] != "":
    #                 layer.css("fill", "#" + v).attr("fill", "#" + v)
    #             if i == 1 and val[i] != "":
    #                 layer.css("stroke", "#" + v).attr("stroke", "#" + v)
    # elements show
    # for key, val in config["show"].items():
    #     if len(val) == 0:
    #         continue
    #     z = solo_letras(val[0])

    #     character.find("[id^='" + z+"']").css("display",
    #                                           "none").attr("display", "none")
    #     show_ele = pq(character.find("[id^='"+val[0]+"']:first"))
    #     show_ele.css("display", "block").attr("display", "block")
    #     show_ele.children("*").css("display",
    #                                "block").attr("display", "block")
    #     show_ele.parents().css("display", "block").attr("display", "block")

    # elements show

    for key, val in config["layers"].items():

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
        show_ele = pq(character.find("[id^='"+val["show"]+"']:first"))
        show_ele.css("display", "block").attr("display", "block")
        show_ele.children("*").css("display", "block").attr("display", "block")
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

    # writing result file
    contents = div_file.html().replace("<body>", "").replace(
        "</body>", "").replace("viewbox=", "viewBox=")
    viewBox = div_file.attr('viewBox')
    if viewBox == None:
        viewBox = div_file.attr('viewbox')
    # print(type(viewBox))
    if "</svg>" not in contents:
        contents = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve" viewBox="' + \
            viewBox + '">' + contents + "</svg>"
    svg_content = '<?xml version="1.0" encoding="utf-8"?>' + contents

    file_path = base_path + os.sep + gender + os.sep + character_id + "_temp.png"

    # svg2png(str.encode(svg_content, 'utf-8'), file_path)
    svg2png(svg_content.encode('utf-8'), file_path)
    return dir_path + os.sep + gender + os.sep + character_id + "_temp.png"


if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("parameter error!")
        exit()
    # first param: book id
    # second param: character id
    # third param : input file path(svg)
    start_time = time.time()
    path = apply_svg(sys.argv[1], sys.argv[2], sys.argv[3])
    print("--- %s seconds ---" % (time.time() - start_time))
    print(path)
