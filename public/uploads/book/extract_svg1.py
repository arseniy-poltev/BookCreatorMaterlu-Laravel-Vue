import sys
import os
import json
import lxml.html
from pyquery import PyQuery as pq
from lxml import etree
from wand.image import Image
from wand.api import library
from wand.color import Color
import codecs


json_layer_path = 'layer_json'


def svg2png1(input_path, output_path):
    with Image() as img:
        with Color('transparent') as bg_color:
            library.MagickSetBackgroundColor(img.wand, bg_color.resource)

        content = open(input_path, 'rb').read()
        # print(content)

        img.read(blob=open(input_path, 'rb').read())
        dest_img = img.make_blob('png')
        with open(output_path, 'wb') as out:
            out.write(dest_img)


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
        for k, v in val.items():
            for option in v:
                if option['tipo'] == 'Mostrar/Ocultar':
                    layers = pq(div_file.find("[id^=" + gender + "]").find(
                        "[id^='" + option['target'] + "']").children("[id^='" + option['extra'] + "']"))
                    for layer in layers:
                        id = pq(layer).attr('id')
                        # save content to file
                        if save_flag:
                            content = pq(layer).html()
                            content = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve" viewBox="' + \
                                viewBox + '">' + content + "</svg>"
                            file_path = base_path + os.sep + gender + os.sep + id + ".svg"
                            f = codecs.open(file_path, "w", 'utf8')
                            f.write('<?xml version="1.0" encoding="utf-8"?>')
                            f.write(content)
                            f.close()
                            # print(str.encode(content, 'utf-8'))

                            png_path = base_path + os.sep + gender + os.sep + id + ".png"
                            svg2png1(file_path, png_path)
                            return

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
