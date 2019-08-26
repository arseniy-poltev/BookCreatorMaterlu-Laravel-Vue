from wand.image import Image
from wand.display import display

with Image(filename='test1.svg') as image:
    image.save(filename='test.png')