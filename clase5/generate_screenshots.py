from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

root = Path(__file__).resolve().parent / 'screenshots'
root.mkdir(exist_ok=True)


def make_image(path: Path, title: str, subtitle: str, form_kind: str) -> None:
    img = Image.new('RGB', (1280, 720), '#f4f7fb')
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((60, 50, 1220, 670), radius=28, fill='#ffffff', outline='#dfeaf5', width=2)

    draw.rounded_rectangle((90, 80, 380, 140), radius=18, fill='#edf5ff')
    draw.text((120, 94), 'Clase 5', fill='#2b6cb0', font=ImageFont.truetype('arial.ttf', 28))
    draw.text((90, 170), title, fill='#1f2937', font=ImageFont.truetype('arial.ttf', 38))
    draw.text((90, 220), subtitle, fill='#5f6c7b', font=ImageFont.truetype('arial.ttf', 24))

    panel_x = 90
    panel_y = 280
    panel_w = 1100
    panel_h = 300
    draw.rounded_rectangle((panel_x, panel_y, panel_x + panel_w, panel_y + panel_h), radius=18, fill='#f9fbff', outline='#dfeaf5', width=2)

    for i in range(3):
        draw.rounded_rectangle((panel_x + 40, panel_y + 40 + i * 65, panel_x + 430, panel_y + 82 + i * 65), radius=12, fill='#ffffff', outline='#dfeaf5', width=2)

    draw.rounded_rectangle((panel_x + 40, panel_y + 230, panel_x + 250, panel_y + 270), radius=12, fill='#2b6cb0')
    draw.text((panel_x + 90, panel_y + 236), 'Enviar formulario', fill='white', font=ImageFont.truetype('arial.ttf', 20))

    if form_kind == 'post':
        draw.text((panel_x + 40, panel_y + 18), 'Formulario por POST', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 28))
        draw.text((panel_x + 470, panel_y + 60), 'Validación del lado del servidor', fill='#2b6cb0', font=ImageFont.truetype('arial.ttf', 24))
        draw.text((panel_x + 470, panel_y + 110), '✓ Nombre obligatorio', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.text((panel_x + 470, panel_y + 150), '✓ Email válido', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.text((panel_x + 470, panel_y + 190), '✓ Mensaje con longitud mínima', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.rounded_rectangle((panel_x + 470, panel_y + 230, panel_x + 980, panel_y + 260), radius=10, fill='#eafaf1', outline='#7bd7aa', width=2)
        draw.text((panel_x + 490, panel_y + 234), 'Éxito: formulario enviado correctamente', fill='#1d8f5f', font=ImageFont.truetype('arial.ttf', 22))
    else:
        draw.text((panel_x + 40, panel_y + 18), 'Formulario por GET', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 28))
        draw.text((panel_x + 460, panel_y + 60), 'Búsqueda segura con filtro', fill='#2b6cb0', font=ImageFont.truetype('arial.ttf', 24))
        draw.text((panel_x + 470, panel_y + 110), '✓ Query sanitizada', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.text((panel_x + 470, panel_y + 150), '✓ Categoría validada', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.text((panel_x + 470, panel_y + 190), '✓ Resultados filtrados', fill='#1f2937', font=ImageFont.truetype('arial.ttf', 20))
        draw.rounded_rectangle((panel_x + 470, panel_y + 230, panel_x + 980, panel_y + 260), radius=10, fill='#e8f9f8', outline='#9adbd7', width=2)
        draw.text((panel_x + 490, panel_y + 234), 'Resultado: 2 coincidencias encontradas', fill='#0f766e', font=ImageFont.truetype('arial.ttf', 22))

    img.save(path)


make_image(root / 'post-form.png', 'Formulario por POST', 'Registro, contacto y validación de entrada', 'post')
make_image(root / 'get-form.png', 'Formulario por GET', 'Búsqueda y filtrado de resultados', 'get')
print('Generated:', ', '.join(str(p.name) for p in sorted(root.iterdir())))
