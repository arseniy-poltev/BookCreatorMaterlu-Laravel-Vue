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


def extract_svg(book_id, input_path, output_name):
    # div_content = open(file=input_path, mode="rb").read()
    div_content = open(input_path, "rb").read()
    div_file = pq(div_content)

    path = os.path.dirname(os.path.realpath(__file__)) + \
        os.sep + book_id + os.sep + json_layer_path

    output = os.path.dirname(os.path.realpath(__file__)) + \
        os.sep + book_id + os.sep + "temp" + os.sep + output_name + '_temp.json'

    configs = []
    for subdir, dirs, files in os.walk(path):
        for file in files:
            if file.endswith("_layer.json"):
                json_path = subdir + os.sep + file
                config = {}
                with open(json_path) as f:
                    config = json.load(f)
                configs.append(config)

    character_cnt = len(configs)
    result = []
    for idx, config in enumerate(configs):
        identifier = ''

        item = {}
        break_flag = False
        if character_cnt > 1:
            identifier = str(idx + 1)
        for gender, val in config["modelo"].items():
            character = div_file.find("[id^='" + gender + identifier + "']")
            if len(character) == 0:
                character = div_file.find("[id^='" + gender + "']")
                break_flag = True
            item[gender] = {}
            for k, v in val.items():
                item[gender][k] = {}
                item[gender][k]['page'] = []
                item[gender][k]['json'] = []
                for option in v:
                    if option['tipo'] == 'Mostrar/Ocultar':
                        layers = pq(character).find(
                            "[id^='" + option['target'] + "']").children("[id^='" + option['extra'] + "']")
                        for layer in layers:
                            id = pq(layer).attr('id')
                            # print(id)
                            item[gender][k]['page'].append(id)
                        # check
                        for value in option['value']:
                            value[1] = "missing"
                            for layer in item[gender][k]['page']:
                                if value[0] in layer:
                                    value[1] = "found"
                                    break
                        item[gender][k]['json'] = option['value']
        result.append(item)
        if break_flag:
            break

    # save result to json file

    # print(result)

    with open(output, "w") as file:
        json.dump(result, file)

    return


# -----------main function-------------
if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("parameter error!")
        exit()
    # first param: book id
    # second param : input file path(svg)
    extract_svg(sys.argv[1], sys.argv[2], sys.argv[3])
