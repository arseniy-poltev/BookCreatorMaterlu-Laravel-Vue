import sys
from wand.image import Image
from wand.api import library
from wand.color import Color


def svg2png(input_path, output_path):
    with Image() as img:
        with Color('transparent') as bg_color:
            library.MagickSetBackgroundColor(img.wand, bg_color.resource)
        img.read(blob=open(input_path, 'rb').read())
        dest_img = img.make_blob('png')
        with open(output_path, 'wb') as out:
            out.write(dest_img)


# -----------main function-------------
if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("parameter error!")
        exit()
    # first param : input file path(svg)
    # second param: output file path(png)
    svg2png(sys.argv[1], sys.argv[2])
