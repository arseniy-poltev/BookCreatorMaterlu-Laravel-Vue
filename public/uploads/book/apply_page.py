import sys
import os
import sys
import io
import json
import lxml.html
from pyquery import PyQuery as pq
from lxml import etree
from wand.image import Image
from wand.api import library
from wand.color import Color
import warnings


warnings.filterwarnings(action='ignore')
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


def find_item(items, query):
    for item in items:
        qitem = pq(item)
        id = qitem.attr("id")
        if id.startswith(query):
            return qitem
    return None


def apply_svg(book_id, file_name, input_path):
    div_content = io.open(input_path, "rb").read()
    div_file = pq(div_content)

    path = os.path.dirname(os.path.realpath(__file__)) + \
        os.sep + book_id
    json_path = path + os.sep + "layer.json"

    base_path = path + os.sep + "temp"
    dir_path = "/uploads/book" + os.sep + book_id + \
        os.sep + "temp"

    # make dir
    if not os.path.exists(base_path):
        os.makedirs(base_path)

    # if not os.path.exists(dir_path):
    #    os.makedirs(dir_path)

    configs = []
    with io.open(file=json_path, encoding="utf-8") as f:
        configs = json.load(f)

    character_cnt = len(configs)
    level_one = div_file.children()

    for config in configs:
        identifier = ''
        if character_cnt > 1:
            identifier = str(config["id"])
        gender = config["gender"]
        character = None
        for item in level_one:
            qitem = pq(item)
            id = qitem.attr("id")
            # hide all characters
            if id.startswith('chica') or id.startswith('chico'):
                qitem.css("display", "none").attr("display", "none")
            if id.startswith(gender + identifier):
                character = qitem
                break

        if character == None:
            identifier = ''

        character = find_item(level_one, gender)

        if character == None:
            continue
        # show selected character
        character.css("display", "block").attr("display", "block")
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
            # show_ele = find_item(
            #     character.children(), "[id^='"+val["show"]+"']:first")
            show_ele = character.children(
                "[id^='"+val["show"]+"']:first")
            if show_ele == None:
                continue

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

    file_path = base_path + os.sep + file_name + "_temp.png"

    # svg2png(str.encode(svg_content, 'utf-8'), file_path)
    svg2png(svg_content.encode('utf-8'), file_path)
    return dir_path + os.sep + file_name + "_temp.png"


if __name__ == "__main__":

    if len(sys.argv) != 4:
        print("parameter error!")
        exit()
    # first param: book id
    # second param: file name
    # third param : input file path(svg)
    path = apply_svg(sys.argv[1], sys.argv[2], sys.argv[3])
    # print(path)
