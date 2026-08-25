from pathlib import Path
from PIL import Image


SOURCE_DIR = Path('/home/ubuntu/webdev-static-assets')
DESTINATION_DIR = Path('/home/ubuntu/atelier-wordpress/atelier/assets/images')
RECIPES = {
    'atelier-hero.png': ('atelier-hero.webp', 1600, 84),
    'atelier-knowledge-map.png': ('atelier-knowledge-map.webp', 1024, 82),
}


def export_webp(source_name: str, output_name: str, max_width: int, quality: int) -> None:
    with Image.open(SOURCE_DIR / source_name) as image:
        image = image.convert('RGB')
        if image.width > max_width:
            height = round(image.height * max_width / image.width)
            image = image.resize((max_width, height), Image.Resampling.LANCZOS)
        image.save(DESTINATION_DIR / output_name, 'WEBP', quality=quality, method=6)


for source, recipe in RECIPES.items():
    export_webp(source, *recipe)
