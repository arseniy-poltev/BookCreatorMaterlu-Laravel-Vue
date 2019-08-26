import sys
import os
import json
import lxml.html

from pyquery import PyQuery as pq
from lxml import etree
from wand.image import Image
from wand.api import library
from wand.color import Color


json_layer_path = 'layer_json'


def svg2png(svg_content, output_path):
    with Image() as img:
        with Color('transparent') as bg_color:
            library.MagickSetBackgroundColor(img.wand, bg_color.resource)
        img.read(blob=svg_content)
        img.resize(100, 100)
        dest_img = img.make_blob('png')
        with open(output_path, 'wb') as out:
            out.write(dest_img)


def extract_svg(book_id, character_id, input_path, save_flag):
    # div_content = open(file=input_path, mode="rb").read()
    div_content = open(input_path, "rb").read()
    div_file = pq(div_content)

    path = os.path.dirname(os.path.realpath(__file__)) + \
        os.sep + book_id + os.sep + json_layer_path
    json_path = path + os.sep + character_id + "_layer.json"

    base_path = path + os.sep + character_id
    # make dir
    if not os.path.exists(base_path):
        os.makedirs(base_path)
    male_path = base_path + os.sep + 'chico'
    female_path = base_path + os.sep + 'chica'

    if not os.path.exists(male_path):
        os.makedirs(male_path)
    if not os.path.exists(female_path):
        os.makedirs(female_path)

    config = {}
    with open(json_path) as f:
        config = json.load(f)

    viewBox = div_file.attr('viewBox')
    if viewBox == None:
        viewBox = div_file.attr('viewbox')

    for gender, val in config["modelo"].items():
        character = div_file.find("[id^=" + gender + "]")
        for k, v in val.items():
            for option in v:
                if option['tipo'] == 'Mostrar/Ocultar':
                    topLayer = pq(character).find(
                        "[id^='" + option['target'] + "']:first")
                    for extra_value in option['value']:

                        layer = pq(topLayer).find(
                            "[id^='" + extra_value[0] + "']:first")
                        id = extra_value[0]

                        layer.css("display", "block").attr(
                            "display", "block")
                        # save content to file
                        if save_flag:
                            content = layer.html()
                            if content == None:
                                content = layer.outer_html()
                            if content == None:
                                continue
                            print(id)

                            content = '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve" viewBox="' + \
                                viewBox + '">' + content + "</svg>"
                            file_path = base_path + os.sep + gender + os.sep + id + ".png"

                            # svg2png(str.encode(content, 'utf-8'), file_path)
                            svg2png(content.encode('utf-8'), file_path)

    return


# -----------main function-------------
if __name__ == "__main__":
    if len(sys.argv) != 5:
        print("parameter error!")
        exit()
    # first param: book id
    # second param: character id
    # third param : input file path(svg)
    # fourth param: save flag
    extract_svg(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4] == "1")
