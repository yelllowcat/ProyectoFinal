#!/usr/bin/env python3
"""
Generates the complete IEEE-830 style documentation PDF for the UNIRED project.
"""

import os
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch, cm
from reportlab.lib.colors import HexColor, black, white, grey
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY, TA_RIGHT
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle,
    KeepTogether, NextPageTemplate, PageTemplate, Frame, BaseDocTemplate
)
from reportlab.platypus.tableofcontents import TableOfContents
from reportlab.platypus.flowables import HRFlowable
from datetime import datetime

# ── Constants ──────────────────────────────────────────────────
OUTPUT = "/media/david/Storage/repos/ProyectoFinal/DOCUMENTACION_UNIRED.pdf"
PAGE_W, PAGE_H = letter  # 8.5 x 11 inches

# Colors
DARK_BLUE = HexColor("#1a3a5c")
MED_BLUE = HexColor("#2c5f8a")
LIGHT_BLUE = HexColor("#e8f0f8")
HEADER_BG = HexColor("#1a3a5c")
TABLE_HEADER_BG = HexColor("#2c5f8a")
TABLE_ALT_ROW = HexColor("#f4f7fb")

# ── Styles ─────────────────────────────────────────────────────
styles = getSampleStyleSheet()

body_style = ParagraphStyle(
    'CustomBody', parent=styles['Normal'],
    fontName='Times-Roman', fontSize=10, leading=14,
    alignment=TA_JUSTIFY, spaceAfter=6
)
body_bold = ParagraphStyle(
    'CustomBodyBold', parent=body_style,
    fontName='Times-Bold'
)
heading1 = ParagraphStyle(
    'CustomH1', parent=styles['Heading1'],
    fontName='Times-Bold', fontSize=14, leading=18,
    textColor=DARK_BLUE, spaceBefore=20, spaceAfter=10,
)
heading2 = ParagraphStyle(
    'CustomH2', parent=styles['Heading2'],
    fontName='Times-Bold', fontSize=12, leading=16,
    textColor=MED_BLUE, spaceBefore=16, spaceAfter=8,
)
heading3 = ParagraphStyle(
    'CustomH3', parent=styles['Heading3'],
    fontName='Times-Bold', fontSize=11, leading=14,
    textColor=DARK_BLUE, spaceBefore=12, spaceAfter=6,
)
center_style = ParagraphStyle(
    'CenterText', parent=body_style, alignment=TA_CENTER
)
right_style = ParagraphStyle(
    'RightText', parent=body_style, alignment=TA_RIGHT
)
caption_style = ParagraphStyle(
    'Caption', parent=body_style,
    fontName='Times-Italic', fontSize=9, leading=12,
    alignment=TA_CENTER, textColor=grey
)
toc_style = ParagraphStyle(
    'TOCEntry', parent=body_style,
    fontSize=10, leading=16, leftIndent=10
)
cover_title = ParagraphStyle(
    'CoverTitle', fontSize=22, leading=28, fontName='Times-Bold',
    alignment=TA_CENTER, textColor=DARK_BLUE, spaceAfter=20
)
cover_subtitle = ParagraphStyle(
    'CoverSubtitle', fontSize=13, leading=18, fontName='Times-Roman',
    alignment=TA_CENTER, spaceAfter=6
)
cover_info = ParagraphStyle(
    'CoverInfo', fontSize=11, leading=16, fontName='Times-Roman',
    alignment=TA_CENTER, spaceAfter=3
)
table_cell = ParagraphStyle(
    'TableCell', parent=body_style, fontSize=9, leading=12,
    spaceAfter=2, spaceBefore=2
)
table_cell_bold = ParagraphStyle(
    'TableCellBold', parent=table_cell, fontName='Times-Bold'
)
table_header_style = ParagraphStyle(
    'TableHeader', parent=table_cell,
    fontName='Times-Bold', textColor=white, fontSize=9
)

# ── Helper functions ───────────────────────────────────────────
def hr():
    return HRFlowable(width="100%", thickness=0.5, color=MED_BLUE, spaceAfter=8, spaceBefore=4)

def heading(text, level=1):
    if level == 1:
        return Paragraph(text, heading1)
    elif level == 2:
        return Paragraph(text, heading2)
    else:
        return Paragraph(text, heading3)

def body(text):
    return Paragraph(text, body_style)

def bold_body(text):
    return Paragraph(text, body_bold)

def make_table(data, col_widths=None, first_row_header=True):
    """Create a styled table."""
    rows = []
    for row in data:
        rows.append([Paragraph(str(c), table_cell) for c in row])

    if first_row_header:
        header_rows = [Paragraph(str(c), table_header_style) for c in data[0]]
        rows[0] = header_rows

    t = Table(rows, colWidths=col_widths, repeatRows=1 if first_row_header else 0)
    style_cmds = [
        ('GRID', (0, 0), (-1, -1), 0.5, MED_BLUE),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]
    if first_row_header:
        style_cmds.append(('BACKGROUND', (0, 0), (-1, 0), TABLE_HEADER_BG))
        style_cmds.append(('TEXTCOLOR', (0, 0), (-1, 0), white))
        style_cmds.append(('FONTNAME', (0, 0), (-1, 0), 'Times-Bold'))

    # Alternating row colors
    for i in range(1, len(data)):
        if i % 2 == 0:
            style_cmds.append(('BACKGROUND', (0, i), (-1, i), TABLE_ALT_ROW))

    t.setStyle(TableStyle(style_cmds))
    return t

def make_ieee_spec_table(data):
    """Creates an IEEE-830 specification table (Campo/Descripción)."""
    rows = []
    for row in data:
        p_row = []
        for cell in row:
            p_row.append(Paragraph(str(cell), table_cell))
        rows.append(p_row)

    # First column header dark, second column light
    styled_rows = []
    for i, row in enumerate(rows):
        if i < len(rows) - 1:  # Not the last row
            styled_rows.append(row)

    t = Table(rows, colWidths=[2.2*inch, 4.3*inch])
    style_cmds = [
        ('GRID', (0, 0), (-1, -1), 0.5, MED_BLUE),
        ('BACKGROUND', (0, 0), (0, -1), TABLE_HEADER_BG),
        ('TEXTCOLOR', (0, 0), (0, -1), white),
        ('FONTNAME', (0, 0), (0, -1), 'Times-Bold'),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('TOPPADDING', (0, 0), (-1, -1), 3),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]
    t.setStyle(TableStyle(style_cmds))
    return t

# ── Content Builders ──────────────────────────────────────────

def build_cover(story):
    """Build the cover page."""
    story.append(Spacer(1, 1.5*inch))

    # UABCS logo (text representation)
    story.append(Paragraph("UNIVERSIDAD AUTÓNOMA DE BAJA CALIFORNIA SUR", cover_title))
    story.append(Spacer(1, 0.1*inch))
    story.append(Paragraph("Departamento Académico de Sistemas Computacionales", cover_subtitle))
    story.append(Paragraph("La Paz, Baja California Sur", cover_subtitle))
    story.append(Spacer(1, 0.6*inch))

    story.append(hr())

    story.append(Spacer(1, 0.3*inch))
    story.append(Paragraph("ASIGNATURA", ParagraphStyle('label', parent=cover_info, fontName='Times-Bold')))
    story.append(Paragraph("Ingeniería de Software II", cover_info))
    story.append(Spacer(1, 0.2*inch))
    story.append(Paragraph("SEMESTRE", ParagraphStyle('label', parent=cover_info, fontName='Times-Bold')))
    story.append(Paragraph("6° Semestre (T.V.)", cover_info))
    story.append(Spacer(1, 0.2*inch))
    story.append(Paragraph("DOCENTE", ParagraphStyle('label', parent=cover_info, fontName='Times-Bold')))
    story.append(Paragraph("Italia Estrada Cota", cover_info))

    story.append(Spacer(1, 0.4*inch))

    story.append(Paragraph("INTEGRANTES", ParagraphStyle('label', parent=cover_info, fontName='Times-Bold')))
    story.append(Paragraph("Gabriel Lauro Hernández", cover_info))
    story.append(Paragraph("Jose Manuel Orozco Vazquez", cover_info))
    story.append(Paragraph("David Gonzalez Vargas", cover_info))

    story.append(Spacer(1, 0.5*inch))
    story.append(hr())

    story.append(Spacer(1, 0.3*inch))
    story.append(Paragraph("SISTEMA INTEGRAL DE RED SOCIAL UNIVERSITARIA", cover_title))
    story.append(Paragraph("UNIRED", ParagraphStyle('proj', parent=cover_title, fontSize=18, textColor=MED_BLUE)))
    story.append(Spacer(1, 0.3*inch))
    story.append(Paragraph("DOCUMENTACIÓN TÉCNICA Y DE ANÁLISIS", cover_subtitle))
    story.append(Spacer(1, 0.2*inch))
    story.append(Paragraph("Versión: 1.0", cover_info))
    story.append(Paragraph("Mayo 2026", cover_info))

    story.append(PageBreak())


def build_toc(story):
    """Build table of contents page."""
    story.append(heading("ÍNDICE", 1))
    story.append(Spacer(1, 0.2*inch))

    toc_items = [
        ("1.", "ESTUDIO DE FACTIBILIDAD"),
        ("  1.1", "Factibilidad técnica"),
        ("    1.1.1", "Hardware"),
        ("    1.1.2", "Software"),
        ("    1.1.3", "Personal técnico requerido"),
        ("  1.2", "Factibilidad operativa"),
        ("  1.3", "Factibilidad económica"),
        ("2.", "RECOPILACIÓN DE INFORMACIÓN: ENTREVISTA"),
        ("  2.1", "Personas entrevistadas"),
        ("  2.2", "Guión de preguntas realizadas"),
        ("  2.3", "Resultados obtenidos de la entrevista"),
        ("  2.4", "Conclusiones de la técnica"),
        ("3.", "TABLA DE ESPECIFICACIONES DE REQUERIMIENTOS"),
        ("4.", "TABLA DE ESPECIFICACIÓN DE REQUERIMIENTOS FUNCIONALES"),
        ("5.", "VALIDACIÓN DE REQUERIMIENTOS"),
        ("6.", "UNIDAD DE ANÁLISIS"),
        ("  6.1", "Módulo de Usuarios"),
        ("  6.2", "Módulo de Publicaciones"),
        ("  6.3", "Módulo de Comentarios"),
        ("  6.4", "Módulo de Amigos"),
        ("  6.5", "Módulo de Administración"),
        ("7.", "DOCUMENTACIÓN: CASOS DE USO Y ACTORES"),
        ("  7.1", "Casos de Uso"),
        ("  7.2", "Actores"),
        ("8.", "UNIDAD DE DISEÑO"),
        ("  8.1", "Diseño de interfaces"),
        ("9.", "BASE DE DATOS"),
        ("  9.1", "Tablas principales"),
        ("  9.2", "Procedimientos almacenados"),
        ("10.", "DIAGRAMAS DE SECUENCIA"),
        ("11.", "DIAGRAMAS DE CLASES"),
        ("12.", "PRUEBAS"),
        ("  12.1", "Pruebas de caja blanca"),
        ("  12.2", "Pruebas de caja negra"),
        ("13.", "PLAN DE IMPLANTACIÓN"),
        ("14.", "MANUAL DE USUARIO"),
    ]
    for num, title in toc_items:
        indent = 10 + (num.count(' ') * 15)
        s = ParagraphStyle('toc', parent=toc_style, leftIndent=indent)
        is_main = num.strip().endswith('.') and not num.startswith(' ')
        story.append(Paragraph(
            f"<b>{num}</b>  {title}" if is_main else f"{num}  {title}",
            ParagraphStyle('toc_entry', parent=s, fontName='Times-Bold' if is_main else 'Times-Roman')
        ))

    story.append(PageBreak())


def build_feasibility(story):
    """Section 1: Estudio de Factibilidad"""
    story.append(heading("1. ESTUDIO DE FACTIBILIDAD", 1))

    story.append(body("""Durante la fase inicial del proyecto, se realizó una investigación exhaustiva dentro de la comunidad universitaria para confirmar que el sistema solicitado (UNIRED) puede desarrollarse e implementarse sin problemas. Esta investigación ayudó a verificar que el nuevo sistema realmente ayudará a las necesidades actuales de los estudiantes, facilitando los procesos de comunicación, interacción académica y social. Además, se revisó que el sistema puede integrarse con los equipos existentes, que el personal está dispuesto a utilizarlo y que los costos de desarrollo son manejables. El análisis se dividió en tres áreas principales: factibilidad técnica, operativa y económica."""))

    # 1.1 Technical Feasibility
    story.append(heading("1.1 Factibilidad técnica", 2))
    story.append(body("""La evaluación técnica se realizó con base en los recursos tecnológicos que actualmente utiliza la comunidad y los que se planean incorporar durante la implementación del sistema."""))

    # 1.1.1 Hardware
    story.append(heading("1.1.1 Hardware", 3))
    story.append(body("""Se verificó que los dispositivos móviles y computadoras utilizadas por los estudiantes cumplen con los requisitos mínimos para ejecutar el sistema web de UNIRED:"""))
    story.append(body("• Equipos con procesador de doble núcleo o superior."))
    story.append(body("• Memoria RAM de al menos 4 GB para una navegación fluida."))
    story.append(body("• Conexión a internet estable (3G/4G o Wi-Fi)."))
    story.append(body("• Servidor de base de datos con al menos 20 GB de almacenamiento SSD, escalable a 100 GB."))
    story.append(body("""Para alojar el sistema se optó por un servidor en la nube (Hostinger), el cual permite acceso seguro y disponibilidad constante."""))

    # 1.1.2 Software
    story.append(heading("1.1.2 Software", 3))
    story.append(body("""Durante la reunión técnica del equipo de desarrollo, se acordó implementar la siguiente arquitectura y stack tecnológico:"""))
    story.append(body("• <b>Back-end:</b> PHP 7.4+, por su facilidad de mantenimiento, escalabilidad en entornos compartidos y gran soporte comunitario."))
    story.append(body("• <b>Front-end:</b> HTML5, CSS3, y JavaScript (Vanilla) enfocado en un diseño minimalista, moderno y responsive."))
    story.append(body("• <b>Base de datos:</b> MySQL, debido a su estructura relacional sólida que se adapta perfectamente al manejo de usuarios, publicaciones, comentarios y relaciones de amistad."))
    story.append(body("• <b>Servidor Web:</b> Apache con mod_rewrite habilitado."))
    story.append(body("• <b>Dependencias:</b> Composer, vlucas/phpdotenv para gestión de variables de entorno, y TCPDF para generación de reportes en PDF."))
    story.append(body("• <b>Gráficos:</b> Chart.js v4 para visualización de estadísticas en el dashboard administrativo."))
    story.append(body("""El equipo definió que, al finalizar el proyecto, se entregará la documentación técnica, manual de usuario y el sistema desplegado en producción."""))

    # 1.1.3 Personnel
    story.append(heading("1.1.3 Personal técnico requerido", 3))
    pers = [
        ["Rol", "Cantidad"],
        ["Analista de Sistemas", "1"],
        ["Diseñador UI/UX", "1"],
        ["Desarrollador Backend PHP", "2"],
        ["Desarrollador Frontend", "2"],
        ["Administrador de Base de Datos (DBA)", "1"],
        ["Ingeniero de Pruebas (QA)", "1"],
    ]
    story.append(make_table(pers, [3*inch, 1.5*inch]))
    story.append(Spacer(1, 0.15*inch))

    # 1.2 Operational Feasibility
    story.append(heading("1.2 Factibilidad operativa", 2))
    story.append(body("""El sistema UNIRED será adoptado fácilmente por la comunidad estudiantil, ya que actualmente experimentan dificultades para centralizar la comunicación universitaria en otras plataformas genéricas. La carga de gestión de contactos disminuirá gracias a la automatización y búsqueda de perfiles dentro del campus. Entre los beneficios operativos identificados se encuentran:"""))
    story.append(body("• La comunicación universitaria estará centralizada en un solo lugar exclusivo para la comunidad."))
    story.append(body("• Los usuarios muestran una altísima disposición a usar el sistema debido a la interfaz intuitiva y los esquemas de colores amigables."))
    story.append(body("• Se utilizará la tipografía Roboto de Google Fonts con pesos 300, 400 y 700 para garantizar jerarquía visual."))
    story.append(body("• El color primario Azul Turquesa (#4db8c4) combinado con blancos limpios y grises oscuros brinda una experiencia agradable."))
    story.append(body("• La interfaz responsive se adapta a dispositivos móviles (320px hasta 1920px)."))
    story.append(body("• Las microinteracciones incluyen transiciones de 0.3s ease-in-out en botones y esqueletos de carga durante peticiones asíncronas."))
    story.append(body("• Se ofrece un dashboard administrativo con métricas y gráficas para la toma de decisiones."))

    # 1.3 Economic Feasibility
    story.append(heading("1.3 Factibilidad económica", 2))
    story.append(body("""Se realizó una estimación del costo total del proyecto comparado con los beneficios que generará para la comunidad universitaria."""))

    costs = [
        ["Concepto", "Costo aproximado"],
        ["Desarrollo del software (equipo de 7 personas)", "$175,000"],
        ["Servidor en la nube, dominio y base de datos", "$6,000"],
        ["Soporte técnico anual", "$12,000"],
        ["Capacitación", "$3,000"],
        ["Total", "$196,000"],
    ]
    story.append(make_table(costs, [3.5*inch, 2*inch]))
    story.append(Spacer(1, 0.15*inch))

    story.append(bold_body("Beneficios esperados"))
    story.append(body("""Durante las entrevistas con la comunidad universitaria se determinaron beneficios reales como:"""))
    story.append(body("• Comunicación centralizada y exclusiva para la comunidad universitaria."))
    story.append(body("• Reducción de la dispersión de información en múltiples plataformas (WhatsApp, Facebook, correo)."))
    story.append(body("• Sistema de publicaciones con soporte multimedia y gestión de contenido."))
    story.append(body("• Red de amistades y contactos gestionable desde un solo lugar."))
    story.append(body("• Dashboard administrativo con estadísticas en tiempo real."))
    story.append(body("• Seguridad en los datos y control de acceso basado en roles."))
    story.append(body("""La comunidad universitaria consideró que los beneficios justifican plenamente la inversión, pues la plataforma permitirá mayor conexión entre estudiantes, docentes y personal administrativo, mejorando la experiencia universitaria integral."""))

    story.append(PageBreak())


def build_interviews(story):
    """Section 2: Recopilación de Información - Entrevistas"""
    story.append(heading("2. RECOPILACIÓN DE INFORMACIÓN: ENTREVISTA", 1))

    # 2.1 Interviewed persons
    story.append(heading("2.1 Personas entrevistadas", 2))
    persons = [
        ["Puesto", "Motivo de selección"],
        ["Estudiante Universitario",
         "Conoce de primera mano las necesidades de comunicación, interacción social y académica dentro del campus."],
        ["Docente Universitario",
         "Aporta información sobre la necesidad de espacios estructurados para compartir conocimiento y materiales académicos."],
        ["Administrador de Sistemas",
         "Responsable de la seguridad, almacenamiento de datos y escalabilidad de la plataforma."],
    ]
    story.append(make_table(persons, [1.8*inch, 4.7*inch]))
    story.append(Spacer(1, 0.1*inch))
    story.append(body("""Estas tres perspectivas permitieron obtener un panorama general del funcionamiento y las necesidades de comunicación dentro de la comunidad universitaria."""))

    # 2.2 Question script
    story.append(heading("2.2 Guión de preguntas realizadas", 2))

    story.append(heading("2.2.1 Preguntas para el Estudiante Universitario", 3))
    q_student = [
        "1. ¿Cuáles son las mayores dificultades que encuentra actualmente en la comunicación universitaria?",
        "2. ¿Qué funcionalidades consideraría indispensables en una red social universitaria?",
        "3. ¿Qué nivel de privacidad espera del sistema?",
        "4. ¿Cómo preferiría gestionar sus contactos y amistades dentro de la plataforma?",
        "5. ¿Qué tipo de contenido le gustaría compartir en sus publicaciones?",
        "6. ¿Considera útil un sistema de comentarios y reacciones en las publicaciones?",
        "7. ¿Qué tan importante es para usted la búsqueda de otros estudiantes por nombre o carrera?",
        "8. ¿Le gustaría poder personalizar su perfil con foto, biografía y datos académicos?",
    ]
    for q in q_student:
        story.append(body(q))

    story.append(heading("2.2.2 Preguntas para el Docente Universitario", 3))
    q_teacher = [
        "1. ¿Cuáles son las mayores dificultades que encuentra actualmente en la comunicación universitaria?",
        "2. ¿Qué funcionalidades consideraría indispensables en una red social universitaria?",
        "3. ¿Considera necesario un espacio donde los alumnos compartan conocimiento de manera estructurada?",
        "4. ¿Qué tipo de supervisión o moderación le gustaría tener sobre el contenido publicado?",
        "5. ¿Cree útil poder crear grupos o foros por materia o carrera?",
        "6. ¿Qué información académica debería mostrarse en los perfiles de los estudiantes?",
        "7. ¿Considera importante la integración con otras herramientas académicas?",
        "8. ¿Qué nivel de seguridad y privacidad espera para los datos de los estudiantes?",
    ]
    for q in q_teacher:
        story.append(body(q))

    story.append(heading("2.2.3 Preguntas para el Administrador de Sistemas", 3))
    q_admin = [
        "1. ¿Cuáles son las mayores dificultades que encuentra actualmente en la comunicación universitaria?",
        "2. ¿Qué funcionalidades consideraría indispensables en una red social universitaria?",
        "3. ¿Qué nivel de privacidad y seguridad espera del sistema?",
        "4. ¿Cómo debe gestionarse el almacenamiento de imágenes de perfil y publicaciones?",
        "5. ¿Qué consideraciones de escalabilidad deben tomarse para la base de datos MySQL?",
        "6. ¿Qué métricas y estadísticas considera necesarias para monitorear la plataforma?",
        "7. ¿Debe el sistema permitir la exportación de reportes en PDF?",
        "8. ¿Qué tan importante es la seguridad y respaldo de la información?",
    ]
    for q in q_admin:
        story.append(body(q))

    # 2.3 Results
    story.append(heading("2.3 Resultados obtenidos de la entrevista", 2))

    story.append(heading("2.3.1 Estudiante Universitario", 3))
    story.append(body("""• Actualmente se utilizan herramientas muy dispersas para la comunicación universitaria (WhatsApp, Facebook, correo electrónico)."""))
    story.append(body("""• No existe un punto centralizado que sea exclusivo para la universidad."""))

    story.append(body("""• Desea un lugar seguro donde todos los miembros sean parte verificada de la comunidad."""))

    story.append(body("""• Considera indispensable un sistema de publicaciones con capacidad de subir fotos, dar "me gusta" y comentar."""))

    story.append(body("""• Requiere un buscador de usuarios eficiente por nombre o carrera."""))

    story.append(body("""• Es crucial la gestión de solicitudes de amistad (enviar, aceptar, rechazar)."""))
    story.append(body("""• Necesita poder editar su perfil de forma segura (nombre, biografía, foto)."""))
    story.append(body("""• Desea poder eliminar su cuenta si así lo decide, con modales de confirmación para evitar accidentes."""))

    story.append(heading("2.3.2 Docente Universitario", 3))
    story.append(body("""• Actualmente la comunicación con alumnos se dispersa en múltiples canales no oficiales."""))

    story.append(body("""• Desea un espacio ordenado donde los alumnos puedan compartir conocimiento de manera estructurada."""))

    story.append(body("""• Considera indispensable un sistema de publicaciones donde se pueda compartir contenido académico."""))

    story.append(body("""• Valora la posibilidad de crear grupos o foros por materia para discusiones académicas."""))

    story.append(body("""• Requiere que el sistema tenga control de roles y permisos para moderación de contenido."""))

    story.append(body("""• Espera un alto nivel de seguridad y privacidad para los datos de los estudiantes."""))

    story.append(heading("2.3.3 Administrador de Sistemas", 3))
    story.append(body("""• Existe una preocupación importante por la seguridad en el manejo de datos personales."""))

    story.append(body("""• Se requiere un control de almacenamiento eficiente para imágenes de perfil y publicaciones (límites de tamaño)."""))

    story.append(body("""• La base de datos MySQL debe estar optimizada para consultas rápidas mediante Stored Procedures."""))

    story.append(body("""• Se necesita un dashboard con métricas como: usuarios registrados, publicaciones, comentarios, actividad diaria."""))

    story.append(body("""• El sistema debe permitir la generación de reportes en PDF con estadísticas relevantes."""))

    story.append(body("""• La arquitectura debe seguir el patrón MVC para facilitar el mantenimiento y la escalabilidad."""))

    story.append(body("""• Debe implementarse autenticación basada en sesiones con contraseñas hasheadas (bcrypt)."""))

    story.append(body("""• Todas las entradas de usuario deben ser sanitizadas para prevenir inyección SQL y XSS."""))

    # 2.4 Conclusions
    story.append(heading("2.4 Conclusiones de la técnica", 2))
    story.append(body("""La entrevista permitió identificar problemas de comunicación y necesidades importantes:"""))
    story.append(body("• Los procesos de comunicación actuales están completamente dispersos en múltiples plataformas genéricas."))
    story.append(body("• No existe un espacio digital centralizado y exclusivo para la comunidad universitaria."))
    story.append(body("• Los estudiantes, docentes y personal administrativo requieren un sistema que organice publicaciones, comentarios, gestión de amistades y perfiles."))
    story.append(body("• Se identificó la necesidad de roles diferenciados (usuario regular y administrador)."))
    story.append(body("• La comunidad demanda un dashboard administrativo con métricas y estadísticas para la toma de decisiones."))
    story.append(body("• La seguridad y privacidad de los datos es una prioridad fundamental para todos los actores."))
    story.append(body("""La información recopilada confirma la necesidad inmediata de implementar un sistema de red social universitaria que modernice y centralice la comunicación dentro del campus."""))

    story.append(PageBreak())


def build_requirements_table(story):
    """Section 3: Tabla de especificaciones de requerimientos"""
    story.append(heading("3. TABLA DE ESPECIFICACIONES DE REQUERIMIENTOS", 1))
    story.append(body("""A continuación se clasifican las preguntas de la entrevista según el tipo de requerimiento (funcional o no funcional):"""))
    story.append(Spacer(1, 0.15*inch))

    reqs = [
        ["Pregunta", "Tipo de requerimiento"],
        ["¿Cuáles son las mayores dificultades que encuentra actualmente en la comunicación universitaria?", "Funcional"],
        ["¿Qué funcionalidades consideraría indispensables en una red social universitaria?", "Funcional"],
        ["¿Qué nivel de privacidad espera del sistema?", "No funcional"],
        ["¿Cómo preferiría gestionar sus contactos y amistades dentro de la plataforma?", "Funcional"],
        ["¿Qué tipo de contenido le gustaría compartir en sus publicaciones?", "Funcional"],
        ["¿Considera útil un sistema de comentarios y reacciones en las publicaciones?", "Funcional"],
        ["¿Qué tan importante es para usted la búsqueda de otros estudiantes por nombre o carrera?", "Funcional"],
        ["¿Le gustaría poder personalizar su perfil con foto, biografía y datos académicos?", "Funcional"],
        ["¿Considera necesario un espacio donde los alumnos compartan conocimiento de manera estructurada?", "Funcional"],
        ["¿Qué tipo de supervisión o moderación le gustaría tener sobre el contenido publicado?", "Funcional"],
        ["¿Cree útil poder crear grupos o foros por materia o carrera?", "Funcional"],
        ["¿Qué información académica debería mostrarse en los perfiles de los estudiantes?", "Funcional"],
        ["¿Considera importante la integración con otras herramientas académicas?", "No funcional"],
        ["¿Qué nivel de seguridad y privacidad espera para los datos de los estudiantes?", "No funcional"],
        ["¿Cómo debe gestionarse el almacenamiento de imágenes de perfil y publicaciones?", "No funcional"],
        ["¿Qué consideraciones de escalabilidad deben tomarse para la base de datos MySQL?", "No funcional"],
        ["¿Qué métricas y estadísticas considera necesarias para monitorear la plataforma?", "Funcional"],
        ["¿Debe el sistema permitir la exportación de reportes en PDF?", "Funcional"],
        ["¿Qué tan importante es la seguridad y respaldo de la información?", "No funcional"],
        ["¿Cómo debe ser el proceso de registro e inicio de sesión?", "Funcional"],
        ["¿Qué información es obligatoria para el registro de un nuevo usuario?", "Funcional"],
        ["¿Debe el sistema tener soporte para dispositivos móviles?", "No funcional"],
        ["¿Qué velocidad de respuesta espera del sistema?", "No funcional"],
        ["¿Es necesario que el sistema tenga un dashboard administrativo?", "Funcional"],
    ]
    story.append(make_table(reqs, [3.5*inch, 2*inch]))
    story.append(PageBreak())


def build_functional_requirements(story):
    """Section 4: Tabla de especificación de requerimientos funcionales"""
    story.append(heading("4. TABLA DE ESPECIFICACIÓN DE REQUERIMIENTOS FUNCIONALES", 1))
    story.append(body("""En esta sección se detallan los requerimientos funcionales del sistema, especificando qué hace cada uno y qué información necesita."""))

    func_reqs = [
        ["¿Qué hace? (Requerimiento funcional)", "¿Qué necesita?"],
        ["Registrar nuevo usuario", "Nombre completo, correo electrónico, contraseña (bcrypt)."],
        ["Iniciar sesión de usuario", "Correo electrónico, contraseña. Validación y regeneración de sesión."],
        ["Cerrar sesión", "Sesión activa. Destrucción de variables de sesión."],
        ["Visualizar perfil de usuario", "ID de usuario. Datos: nombre, biografía, foto, fecha de registro."],
        ["Editar perfil de usuario", "Nombre completo, biografía, foto de perfil (JPEG/PNG/GIF, máximo 5MB)."],
        ["Eliminar cuenta de usuario", "Confirmación de eliminación. Eliminación en cascada de todos los datos."],
        ["Crear publicación", "Contenido de texto, imagen opcional (JPEG/PNG/GIF, máximo 5MB)."],
        ["Editar publicación", "ID de publicación, nuevo contenido de texto y/o imagen."],
        ["Eliminar publicación", "ID de publicación. Verificación de ownership. Soft delete."],
        ["Dar 'Me gusta' a publicación", "ID de publicación, ID de usuario. Toggle like/unlike."],
        ["Agregar comentario a publicación", "ID de publicación, ID de usuario, contenido del comentario."],
        ["Eliminar comentario", "ID de comentario. Verificación de ownership. Soft delete."],
        ["Agregar respuesta a comentario", "ID de comentario, ID de usuario, contenido de la respuesta."],
        ["Eliminar respuesta", "ID de respuesta. Verificación de ownership. Soft delete."],
        ["Dar 'Me gusta' a comentario", "ID de comentario, ID de usuario. Toggle like/unlike."],
        ["Dar 'Me gusta' a respuesta", "ID de respuesta, ID de usuario. Toggle like/unlike."],
        ["Carga paginada de comentarios", "ID de publicación. Carga progresiva de 3 en 3 comentarios."],
        ["Enviar solicitud de amistad", "ID del usuario receptor. Verificación de estado previo."],
        ["Aceptar solicitud de amistad", "ID de solicitud. Cambio de estado a 'accepted'. Creación de registro en friends."],
        ["Rechazar solicitud de amistad", "ID de solicitud. Cambio de estado a 'rejected'."],
        ["Cancelar solicitud de amistad", "ID del usuario receptor. Cambio de estado a 'cancelled'."],
        ["Eliminar amigo", "ID de amistad o ID de usuario. Eliminación del registro en friends."],
        ["Buscar usuarios", "Nombre o término de búsqueda. Filtrado por coincidencia."],
        ["Ver lista de amigos", "ID de usuario. Lista de relaciones de amistad confirmadas."],
        ["Ver solicitudes pendientes", "ID de usuario. Solicitudes entrantes con estado 'pending'."],
        ["Sugerencias de usuarios", "ID de usuario. Algoritmo que excluye amigos y solicitudes existentes."],
        ["Dashboard administrativo", "Rol de administrador. Estadísticas: total usuarios, posts, comentarios, actividad."],
        ["Gráficas de actividad", "Datos históricos. Chart.js para visualización de timeline y métricas."],
        ["Generar reporte PDF", "Datos estadísticos. TCPDF para generación de reporte descargable."],
        ["Registro de auditoría de perfil", "Datos anteriores y nuevos del perfil. Trigger BEFORE UPDATE en tabla users."],
        ["Visualización de imágenes en modal", "Ruta de imagen. Modal de pantalla completa con overlay."],
    ]
    story.append(make_table(func_reqs, [3*inch, 3.5*inch]))
    story.append(Spacer(1, 0.2*inch))

    # Detailed IEEE-830 specs for main RFs
    story.append(heading("4.1 Especificación detallada de requerimientos principales (IEEE-830)", 2))

    specs = [
        ("RF-01", "Registro de Usuario", "Funcional",
         "El sistema permitirá a un nuevo estudiante registrarse en la red social universitaria proporcionando sus datos básicos.",
         "Nombre completo, correo electrónico, contraseña.",
         "Validación de formato de correo, hash de contraseña con bcrypt, inserción en BD mediante Stored Procedure sp_register_user.",
         "Confirmación visual de registro exitoso y redirección al inicio de sesión.",
         "Usuario Regular / Estudiante", "No estar autenticado previamente.", "Usuario registrado y redirigido al login."),

        ("RF-02", "Inicio de Sesión", "Funcional",
         "El sistema autenticará a un usuario con sus credenciales (correo y contraseña).",
         "Correo electrónico, contraseña.",
         "Verificación de credenciales mediante sp_login_user, validación con password_verify(), regeneración de ID de sesión.",
         "Redirección al feed de publicaciones (rol user) o dashboard (rol admin).",
         "Usuario Regular / Administrador", "Tener una cuenta registrada y activa.", "Sesión iniciada con variables $_SESSION pobladas."),

        ("RF-03", "Crear Publicación", "Funcional",
         "El sistema permitirá al usuario crear una publicación con texto y opcionalmente una imagen.",
         "Contenido de texto, imagen (opcional, JPEG/PNG/GIF, máximo 5MB).",
         "Validación de tipo y tamaño de archivo, sanitización de texto, inserción mediante sp_create_post, almacenamiento de imagen en assets/imagesPosts/.",
         "Publicación renderizada en el feed general con datos del autor y timestamp.",
         "Usuario Regular", "Estar autenticado.", "Post guardado en BD y visible en el feed."),

        ("RF-04", "Sistema de Comentarios", "Funcional",
         "El sistema permitirá agregar comentarios a las publicaciones y visualizarlos con paginación progresiva.",
         "ID de publicación, contenido del comentario.",
         "Inserción mediante sp_create_comment, carga paginada de 3 en 3 mediante AJAX/Fetch.",
         "Comentario visible bajo la publicación con nombre y foto del autor.",
         "Usuario Regular", "Publicación existente y sesión iniciada.", "Comentario agregado al post con paginación actualizada."),

        ("RF-05", "Sistema de Amigos", "Funcional",
         "El sistema gestionará el flujo bidireccional de solicitudes de amistad: enviar, aceptar, rechazar y cancelar.",
         "ID de usuario destino, ID de solicitud.",
         "Validación de estado previo, inserción en friend_requests, actualización de estado, creación en friends al aceptar.",
         "Notificación visual del estado de la solicitud y actualización de listas.",
         "Usuario Regular", "Estar autenticado. No tener solicitud pendiente previa.", "Estado de amistad actualizado en BD."),

        ("RF-06", "Editar Perfil", "Funcional",
         "El sistema permitirá al usuario modificar su nombre completo, biografía y foto de perfil.",
         "Nombre completo, biografía, archivo de imagen (JPEG/PNG/GIF, máximo 5MB).",
         "Validación de campos, carga de imagen a assets/imagesProfile/, actualización en BD, trigger de auditoría.",
         "Perfil actualizado con confirmación visual y registro en user_update_log.",
         "Usuario Regular", "Estar autenticado.", "Datos actualizados en tabla users y renderizados instantáneamente."),

        ("RF-07", "Dashboard Administrativo", "Funcional",
         "El sistema proveerá un panel de control con estadísticas, gráficas y tablas de datos para el administrador.",
         "Rol de administrador, datos agregados de BD.",
         "Consultas a BD mediante AdminModel: getSummaryStats, getActivityTimeline, getEngagementBreakdown, etc.",
         "Dashboard con tarjetas de resumen, gráficas Chart.js (timeline, posts por día, crecimiento de usuarios, etc.) y tablas de datos.",
         "Administrador", "Estar autenticado con rol 'admin'.", "Dashboard renderizado con métricas actualizadas."),

        ("RF-08", "Generación de Reporte PDF", "Funcional",
         "El sistema generará un reporte descargable en PDF con las estadísticas más relevantes de la plataforma.",
         "Datos estadísticos de la BD.",
         "Recopilación de datos mediante AdminModel, generación de PDF con TCPDF, headers para descarga.",
         "Archivo PDF descargable con usuarios con más posts, más amigos, posts con más comentarios y más likes.",
         "Administrador", "Estar autenticado con rol 'admin'. Datos existentes en BD.", "PDF generado y descargado por el navegador."),
    ]

    for spec in specs:
        data = [
            ["Campo", "Descripción"],
            ["ID del requerimiento", spec[0]],
            ["Nombre o título", spec[1]],
            ["Tipo", spec[2]],
            ["Descripción", spec[3]],
            ["Entradas", spec[4]],
            ["Procesamiento", spec[5]],
            ["Salidas", spec[6]],
            ["Actores involucrados", spec[7]],
            ["Precondiciones", spec[8]],
            ["Postcondiciones", spec[9]],
        ]
        story.append(make_ieee_spec_table(data))
        story.append(Spacer(1, 0.15*inch))

    story.append(PageBreak())


def build_validation(story):
    """Section 5: Validación de requerimientos"""
    story.append(heading("5. VALIDACIÓN DE REQUERIMIENTOS", 1))
    story.append(body("""En esta sección se validan los requerimientos funcionales del sistema, verificando que sean no ambiguos, relevantes, consistentes y completos."""))

    story.append(Spacer(1, 0.1*inch))

    validations = [
        ["Requerimiento", "No ambiguo", "Relevante", "Consistente", "Completo"],
        ["RF-01 — Registro de Usuario", "✓", "✓", "✓", "✓"],
        ["RF-02 — Inicio de Sesión", "✓", "✓", "✓", "✓"],
        ["RF-03 — Crear Publicación", "✓", "✓", "✓", "✓"],
        ["RF-04 — Sistema de Comentarios", "✓", "✓", "✓", "✓"],
        ["RF-05 — Sistema de Amigos", "✓", "✓", "✓", "✓"],
        ["RF-06 — Editar Perfil", "✓", "✓", "✓", "✓"],
        ["RF-07 — Dashboard Administrativo", "✓", "✓", "✓", "✓"],
        ["RF-08 — Generación de Reporte PDF", "✓", "✓", "✓", "✓"],
        ["RF-09 — Eliminar Publicación", "✓", "✓", "✓", "✓"],
        ["RF-10 — Dar Me Gusta a Publicación", "✓", "✓", "✓", "✓"],
        ["RF-11 — Enviar Solicitud de Amistad", "✓", "✓", "✓", "✓"],
        ["RF-12 — Aceptar/Rechazar Amistad", "✓", "✓", "✓", "✓"],
        ["RF-13 — Búsqueda de Usuarios", "✓", "✓", "✓", "✓"],
        ["RF-14 — Carga Paginada de Comentarios", "✓", "✓", "✓", "✓"],
        ["RF-15 — Eliminar Cuenta", "✓", "✓", "✓", "✓"],
        ["RF-16 — Cerrar Sesión", "✓", "✓", "✓", "✓"],
        ["RF-17 — Visualizar Perfil", "✓", "✓", "✓", "✓"],
        ["RF-18 — Sugerencias de Usuarios", "✓", "✓", "✓", "✓"],
        ["RF-19 — Likes en Comentarios", "✓", "✓", "✓", "✓"],
        ["RF-20 — Likes en Respuestas", "✓", "✓", "✓", "✓"],
        ["RF-21 — Auditoría de Cambios de Perfil", "✓", "✓", "✓", "✓"],
        ["RF-22 — Visualización de Imágenes en Modal", "✓", "✓", "✓", "✓"],
        ["RF-23 — Gráficas de Actividad", "✓", "✓", "✓", "✓"],
        ["RF-24 — Diseño Responsive", "✓", "✓", "✓", "✓"],
    ]
    story.append(make_table(validations, [2.8*inch, 0.8*inch, 0.8*inch, 0.9*inch, 0.8*inch]))
    story.append(PageBreak())


def build_analysis_unit(story):
    """Section 6: Unidad de Análisis"""
    story.append(heading("6. UNIDAD DE ANÁLISIS", 1))
    story.append(body("""La arquitectura de UNIRED se compone de múltiples módulos interconectados, diseñados bajo el patrón MVC (Modelo-Vista-Controlador) con soporte para operaciones asíncronas mediante AJAX/Fetch y una API RESTful."""))

    modules = [
        ("6.1 Módulo de Usuarios",
         """Encargado de gestionar el registro, inicio de sesión seguro y personalización de perfiles. Valida contraseñas mediante bcrypt, administra sesiones con regeneración de ID, y permite modificar fotos de perfil (hasta 5 MB), nombre completo y biografía. Incluye un trigger de auditoría (trg_user_update_log) que registra todos los cambios realizados al perfil en la tabla user_update_log. El módulo también gestiona la eliminación de cuenta con borrado en cascada de todos los datos asociados (publicaciones, comentarios, likes, amistades)."""),
        ("6.2 Módulo de Publicaciones",
         """El corazón de la red social. Permite crear publicaciones de texto y multimedia. Incluye capacidades avanzadas para subir imágenes con validaciones de tipo (JPEG, PNG, GIF), edición posterior y eliminación segura mediante soft delete (bandera active). Integra un modal de pantalla completa para visualización de imágenes. Implementa el sistema de 'Me gusta' con toggle like/unlike y contador en tiempo real. Utiliza la vista v_posts_stats que combina posts con información del autor, conteo de likes y conteo de comentarios para renderizado eficiente del feed."""),
        ("6.3 Módulo de Comentarios",
         """Sub-módulo interactivo que permite añadir respuestas a publicaciones. Utiliza paginación progresiva (de 3 en 3) para mejorar el rendimiento de la carga inicial, con botón 'Cargar más comentarios'. Incluye enlaces dinámicos a los perfiles de los autores. Soporta eliminación de comentarios propios (soft delete) y carga dinámica. Integra un sistema de respuestas anidadas (replies) con funcionalidad similar, y sistema de likes independiente para comentarios y respuestas."""),
        ("6.4 Módulo de Amigos",
         """Gestor de redes de contactos. Soporta flujo bidireccional de solicitudes de amistad (Enviar, Aceptar, Rechazar, Cancelar). Provee un motor de búsqueda de perfiles públicos por nombre y un algoritmo de sugerencias que excluye amigos existentes y solicitudes pendientes. Administra la lista de contactos con opción de eliminar amigo. Utiliza dos tablas: friend_requests para el flujo de solicitudes y friends para las relaciones confirmadas, con constraint UNIQUE para evitar duplicados."""),
        ("6.5 Módulo de Administración",
         """Panel de control exclusivo para usuarios con rol 'admin'. Proporciona un dashboard con 5 tarjetas de resumen (total usuarios, publicaciones, comentarios, likes, amistades), gráficas interactivas generadas con Chart.js (activity timeline, engagement breakdown, user growth, user activity split, posts by day of week, post image ratio, top engaged users) y 4 tablas de datos con los rankings de usuarios y publicaciones más activos. Incluye generación de reporte PDF descargable mediante TCPDF con las estadísticas más relevantes."""),
    ]

    for title, desc in modules:
        story.append(heading(title, 2))
        story.append(body(desc))
        story.append(Spacer(1, 0.08*inch))

    story.append(PageBreak())


def build_use_cases(story):
    """Section 7: Documentación - Casos de Uso y Actores"""
    story.append(heading("7. DOCUMENTACIÓN: CASOS DE USO Y ACTORES", 1))

    # 7.1 Use Cases
    story.append(heading("7.1 Casos de Uso", 2))
    story.append(body("""A continuación se presentan los casos de uso principales del sistema UNIRED, documentados según el formato estándar."""))

    # CU-01: Registro de Usuario
    story.append(heading("7.1.1 Caso de Uso - Registro de Usuario", 3))
    cu01 = [
        ["Campo", "Descripción"],
        ["Identificador", "CU-01"],
        ["Nombre", "Registro de Usuario"],
        ["Actor Principal", "Estudiante / Usuario Regular"],
        ["Descripción", "Permite a un nuevo estudiante registrarse en la red social universitaria."],
        ["Precondición", "No estar autenticado previamente en el sistema."],
        ["Postcondición", "Usuario registrado en la base de datos y redirigido a la página de inicio de sesión."],
    ]
    story.append(make_ieee_spec_table(cu01))
    story.append(Spacer(1, 0.1*inch))
    story.append(bold_body("Secuencia Normal:"))
    seq = [
        ["Paso", "Acción"],
        ["1", "El usuario navega a la página de registro (/register)."],
        ["2", "El sistema muestra el formulario con campos: nombre completo, correo, contraseña."],
        ["3", "El usuario ingresa sus datos y envía el formulario."],
        ["4", "El cliente valida los campos requeridos usando JavaScript."],
        ["5", "Se envía petición POST asíncrona (AJAX/Fetch) al controlador AuthController."],
        ["6", "El controlador sanitiza los datos y ejecuta sp_register_user en MySQL."],
        ["7", "La contraseña se hashea con bcrypt antes del almacenamiento."],
        ["8", "El sistema retorna JSON con confirmación o errores de validación."],
    ]
    story.append(make_table(seq, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.1*inch))

    exc = [
        ["Paso", "Acción"],
        ["1", "Si el correo ya existe, el sistema retorna error 'El correo ya está registrado'."],
        ["2", "Si hay campos vacíos, se muestran mensajes de validación."],
    ]
    story.append(bold_body("Excepciones:"))
    story.append(make_table(exc, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.15*inch))

    story.append(bold_body("Rendimiento:"))
    story.append(body("Cota de tiempo: 2 segundos. Frecuencia esperada: 50 veces por semana."))
    story.append(body("Importancia: Muy importante. Urgencia: Inmediatamente."))

    story.append(Spacer(1, 0.3*inch))

    # CU-02: Iniciar Sesión
    story.append(heading("7.1.2 Caso de Uso - Inicio de Sesión", 3))
    cu02 = [
        ["Campo", "Descripción"],
        ["Identificador", "CU-02"],
        ["Nombre", "Inicio de Sesión"],
        ["Actor Principal", "Estudiante / Usuario Regular / Administrador"],
        ["Descripción", "Autentica a un usuario con sus credenciales (correo y contraseña)."],
        ["Precondición", "Tener una cuenta registrada y activa en el sistema."],
        ["Postcondición", "Sesión iniciada, acceso al feed de publicaciones (user) o dashboard (admin)."],
    ]
    story.append(make_ieee_spec_table(cu02))
    story.append(Spacer(1, 0.1*inch))
    story.append(bold_body("Secuencia Normal:"))
    seq2 = [
        ["Paso", "Acción"],
        ["1", "El usuario navega a la página de inicio de sesión (/login)."],
        ["2", "El sistema muestra formulario con campos: correo, contraseña."],
        ["3", "El usuario ingresa credenciales y envía el formulario."],
        ["4", "El cliente valida campos requeridos con JavaScript."],
        ["5", "Se envía petición POST al AuthController."],
        ["6", "El controlador ejecuta sp_login_user para verificar existencia del correo."],
        ["7", "Se verifica la contraseña con password_verify() contra el hash bcrypt."],
        ["8", "El sistema regenera el ID de sesión y establece variables $_SESSION."],
        ["9", "El usuario es redirigido según su rol (user → /posts, admin → /dashboard)."],
    ]
    story.append(make_table(seq2, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.1*inch))

    exc2 = [
        ["Paso", "Acción"],
        ["1", "Si el correo no existe: mensaje 'Correo no encontrado'."],
        ["2", "Si la contraseña es incorrecta: mensaje 'Contraseña incorrecta'."],
        ["3", "Si la cuenta está inactiva: mensaje 'Cuenta desactivada'."],
    ]
    story.append(bold_body("Excepciones:"))
    story.append(make_table(exc2, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.15*inch))

    story.append(bold_body("Rendimiento:"))
    story.append(body("Cota de tiempo: 2 segundos. Frecuencia esperada: 200 veces por día."))
    story.append(body("Importancia: Muy importante. Urgencia: Inmediatamente."))
    story.append(Spacer(1, 0.3*inch))

    # CU-03: Crear Publicación
    story.append(heading("7.1.3 Caso de Uso - Crear Publicación", 3))
    cu03 = [
        ["Campo", "Descripción"],
        ["Identificador", "CU-03"],
        ["Nombre", "Crear Publicación"],
        ["Actor Principal", "Estudiante / Usuario Regular"],
        ["Descripción", "El usuario redacta un texto o sube una imagen al feed general de la red social."],
        ["Precondición", "Estar autenticado en el sistema."],
        ["Postcondición", "Publicación guardada en la base de datos y visible para amigos en el feed."],
    ]
    story.append(make_ieee_spec_table(cu03))
    story.append(Spacer(1, 0.1*inch))
    story.append(bold_body("Secuencia Normal:"))
    seq3 = [
        ["Paso", "Acción"],
        ["1", "El usuario navega a la sección 'Agregar Post' (/addPost)."],
        ["2", "El sistema muestra formulario con campos: contenido (textarea) e imagen (opcional)."],
        ["3", "El usuario ingresa el texto y opcionalmente selecciona una imagen."],
        ["4", "El cliente valida tipos de archivo (JPEG, PNG, GIF) y tamaño (máx. 5MB)."],
        ["5", "Se envía petición POST con FormData al PostController."],
        ["6", "El controlador sanitiza el texto, procesa la imagen y ejecuta sp_create_post."],
        ["7", "La imagen se almacena en assets/imagesPosts/ con nombre único."],
        ["8", "El sistema retorna JSON con confirmación y redirige al feed."],
    ]
    story.append(make_table(seq3, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.1*inch))

    exc3 = [
        ["Paso", "Acción"],
        ["1", "Si el archivo no es imagen válida: mensaje de error de formato."],
        ["2", "Si el archivo excede 5MB: mensaje de error de tamaño."],
        ["3", "Si el contenido está vacío y no hay imagen: mensaje de validación."],
    ]
    story.append(bold_body("Excepciones:"))
    story.append(make_table(exc3, [0.8*inch, 5.7*inch]))
    story.append(Spacer(1, 0.15*inch))

    story.append(bold_body("Rendimiento:"))
    story.append(body("Cota de tiempo: 3 segundos. Frecuencia esperada: 100 veces por día."))
    story.append(body("Importancia: Muy importante. Urgencia: Inmediatamente."))

    story.append(Spacer(1, 0.3*inch))

    # CU-04 through CU-10 (summarized)
    use_cases_summary = [
        ("7.1.4", "Caso de Uso - Comentar Publicación", "CU-04",
         "El usuario agrega un comentario en una publicación existente.",
         "Publicación existente y sesión iniciada.", "Comentario visible bajo la publicación con paginación."),

        ("7.1.5", "Caso de Uso - Dar Me Gusta", "CU-05",
         "El usuario reacciona positivamente a una publicación, comentario o respuesta.",
         "Sesión iniciada. Elemento existente.", "Contador de likes incrementado/decrementado en BD."),

        ("7.1.6", "Caso de Uso - Enviar Solicitud de Amistad", "CU-06",
         "El usuario busca otro perfil y envía una solicitud para conectar.",
         "Perfil público encontrado. No tener solicitud pendiente.", "Solicitud en estado 'pending' en friend_requests."),

        ("7.1.7", "Caso de Uso - Aceptar/Rechazar Amistad", "CU-07",
         "El receptor acepta o rechaza una solicitud de amistad pendiente.",
         "Tener solicitud entrante con estado 'pending'.", "Ambos usuarios en tabla friends (si acepta) o solicitud cerrada (si rechaza)."),

        ("7.1.8", "Caso de Uso - Editar Perfil", "CU-08",
         "El usuario modifica su nombre completo, biografía o foto de perfil.",
         "Estar autenticado.", "Datos actualizados en users y registro de auditoría creado."),

        ("7.1.9", "Caso de Uso - Eliminar Publicación", "CU-09",
         "El autor de una publicación la elimina mediante soft delete.",
         "Ser el propietario de la publicación.", "Publicación marcada como inactiva (active=0)."),

        ("7.1.10", "Caso de Uso - Búsqueda de Usuarios", "CU-10",
         "Motor de búsqueda para encontrar usuarios por nombre o correo.",
         "Estar autenticado.", "Lista de tarjetas de usuario renderizada con opciones de amistad."),
    ]

    for num, title, cu_id, desc, pre, post in use_cases_summary:
        story.append(heading(f"{num} {title}", 3))
        data = [
            ["Campo", "Descripción"],
            ["Identificador", cu_id],
            ["Nombre", title.replace("Caso de Uso - ", "")],
            ["Actor Principal", "Estudiante / Usuario Regular"],
            ["Descripción", desc],
            ["Precondición", pre],
            ["Postcondición", post],
        ]
        story.append(make_ieee_spec_table(data))
        story.append(Spacer(1, 0.15*inch))

    story.append(PageBreak())

    # 7.2 Actores
    story.append(heading("7.2 Actores del Sistema", 2))

    story.append(heading("Actor #1: Estudiante / Usuario Regular", 3))
    actor1 = [
        ["Campo", "Descripción"],
        ["Actor", "Estudiante / Usuario Regular"],
        ["Tipo", "Primario"],
        ["Casos de Uso",
         "CU-01 Registro de Usuario\nCU-02 Inicio de Sesión\nCU-03 Crear Publicación\nCU-04 Comentar Publicación\nCU-05 Dar Me Gusta\nCU-06 Enviar Solicitud de Amistad\nCU-07 Aceptar/Rechazar Amistad\nCU-08 Editar Perfil\nCU-09 Eliminar Publicación\nCU-10 Búsqueda de Usuarios"],
        ["Descripción",
         "El Usuario Regular es el estudiante universitario que utiliza el sistema para conectarse con la comunidad, compartir publicaciones, interactuar mediante comentarios y likes, gestionar su red de amistades y personalizar su perfil. Es el actor principal y más frecuente del sistema."],
    ]
    story.append(make_ieee_spec_table(actor1))
    story.append(Spacer(1, 0.2*inch))

    story.append(heading("Actor #2: Administrador", 3))
    actor2 = [
        ["Campo", "Descripción"],
        ["Actor", "Administrador"],
        ["Tipo", "Primario"],
        ["Casos de Uso",
         "CU-02 Inicio de Sesión (rol admin)\nVisualizar Dashboard Administrativo\nConsultar Estadísticas y Gráficas\nGenerar Reporte PDF\nVer Rankings de Usuarios y Publicaciones"],
        ["Descripción",
         "El Administrador tiene credenciales especiales (admin@gmail.com) que le otorgan acceso al dashboard administrativo. Desde allí puede visualizar métricas generales de la plataforma, monitorear la actividad de los usuarios, analizar gráficas de tendencias, consultar rankings de usuarios y publicaciones más activos, y generar reportes PDF descargables con estadísticas. No tiene acceso al feed social como usuario regular."],
    ]
    story.append(make_ieee_spec_table(actor2))

    story.append(PageBreak())


def build_design(story):
    """Section 8: Unidad de Diseño"""
    story.append(heading("8. UNIDAD DE DISEÑO", 1))

    story.append(heading("8.1 Diseño de Interfaces", 2))
    story.append(body("""El enfoque de diseño de UNIRED está altamente centrado en la usabilidad y la estética moderna. A continuación, se describen los elementos visuales y decisiones de diseño clave:"""))

    story.append(Spacer(1, 0.1*inch))

    design_items = [
        ("Tipografía", "Roboto (Google Fonts) en múltiples pesos (300, 400, 700) para garantizar jerarquía visual. Los títulos usan peso 700, el cuerpo 400 y textos secundarios 300."),
        ("Paleta de Colores",
         "Color primario: Azul Turquesa (#4db8c4). Fondos: blanco (#ffffff) y gris claro (#f4f6f9). Texto principal: gris oscuro (#333333). Texto secundario: gris medio (#666666). Alertas de error: rojo suave, confirmaciones: verde."),
        ("Layout Desktop",
         "Sidebar fija a la izquierda (250px) con navegación principal y foto de perfil. Feed central scrolleable con ancho máximo de 680px. En dashboard admin, widgets de estadísticas y gráficas en grid adaptable."),
        ("Layout Móvil",
         "La sidebar se transforma en un drawer oculto que se despliega al interactuar con el botón de menú (hamburguesa). Las publicaciones ocupan el ancho completo. Las tablas de datos se vuelven scrollables horizontalmente."),
        ("Sidebar",
         "Componente reutilizable (views/assets/sidebar.php) incluido en todas las vistas autenticadas. Contiene: foto de perfil circular, nombre del usuario, enlaces de navegación con iconos SVG (Inicio, Amigos, Perfil, Agregar Post, Cerrar Sesión). En móvil incluye overlay semitransparente."),
        ("Publicaciones (Posts)",
         "Tarjetas blancas con sombra sutil (box-shadow). Cabecera con foto y nombre del autor + timestamp. Cuerpo con texto y/o imagen. Pie con botones de Like (corazón), Comentarios y menú de opciones (editar/eliminar, solo para el autor)."),
        ("Comentarios",
         "Sección colapsable debajo de cada publicación. Muestra los primeros 3 comentarios con botón 'Cargar más'. Cada comentario muestra foto del autor (30px circular), nombre con enlace al perfil, texto y timestamp. Incluye campo de texto para nuevo comentario y botón de envío."),
        ("Modales",
         "Modal de confirmación para acciones críticas (eliminar cuenta, eliminar publicación, eliminar amigo) con overlay oscuro y botones de Confirmar/Cancelar. Modal de pantalla completa para visualización de imágenes con botón de cierre."),
        ("Dashboard Admin",
         "5 tarjetas de resumen (KPI cards) con iconos, valor numérico grande y etiqueta. Sección de gráficas: gráfico de líneas para actividad, gráfico de barras para engagement, gráfico de dona para distribución, gráfico de barras horizontales para días de la semana. 4 tablas de datos con rankings."),
        ("Formularios",
         "Campos con borde sutil, enfoque con resaltado azul turquesa. Validación en tiempo real con JavaScript. Mensajes de error en rojo debajo del campo. Botones con transiciones de 0.3s ease-in-out y efecto hover."),
        ("Microinteracciones",
         "Transiciones de 0.3s ease-in-out en botones (hover: ligero cambio de opacidad). Hover en tarjetas de publicaciones (ligera elevación de sombra). Esqueletos de carga (skeleton screens) durante peticiones asíncronas. Animación de corazón al dar like. Toast de notificaciones para confirmaciones."),
    ]

    for title, desc in design_items:
        story.append(bold_body(f"• {title}:"))
        story.append(body(desc))
        story.append(Spacer(1, 0.06*inch))

    story.append(Spacer(1, 0.15*inch))
    story.append(body("""Todas las interfaces fueron diseñadas siguiendo el enfoque Mobile First, garantizando una experiencia de usuario óptima en dispositivos móviles (desde 320px), tablets (768px) y escritorio (1024px+)."""))

    story.append(PageBreak())


def build_database(story):
    """Section 9: Base de Datos"""
    story.append(heading("9. BASE DE DATOS", 1))
    story.append(body("""La estructura de base de datos relacional de UNIRED está optimizada para la integridad referencial y las consultas veloces mediante Stored Procedures. Se utiliza el motor InnoDB de MySQL con codificación utf8mb4_unicode_ci para soporte completo de caracteres."""))

    story.append(heading("9.1 Tablas Principales", 2))

    tables = [
        ("users", "Almacena la información de los usuarios registrados. Clave primaria: user_id. Campos: full_name, biography, profile_picture, email (UNIQUE), password (hash bcrypt), registration_date, role (user/admin), active (boolean), updated_at."),
        ("posts", "Publicaciones creadas por los usuarios. Clave primaria: post_id. Clave foránea: user_id → users(user_id) ON DELETE CASCADE. Campos: content, image, created_at, updated_at, active (soft delete)."),
        ("comments", "Comentarios en publicaciones. Clave primaria: comment_id. Claves foráneas: post_id → posts(post_id), user_id → users(user_id). Campos: content, created_at, active."),
        ("replies", "Respuestas anidadas a comentarios. Clave primaria: reply_id. Claves foráneas: comment_id → comments(comment_id), user_id → users(user_id). Campos: content, created_at, active."),
        ("likes", "Registro de 'Me gusta' en publicaciones. Clave primaria: like_id. Claves foráneas: post_id, user_id. Restricción UNIQUE(post_id, user_id) para evitar duplicados."),
        ("comment_likes", "Likes en comentarios. Similar a likes pero referenciando comment_id. UNIQUE(comment_id, user_id)."),
        ("reply_likes", "Likes en respuestas. Similar pero referenciando reply_id. UNIQUE(reply_id, user_id)."),
        ("friend_requests", "Solicitudes de amistad. Clave primaria: request_id. Claves foráneas: sender_id, receiver_id → users(user_id). Campos: status (pending/accepted/rejected/cancelled), request_date, response_date."),
        ("friends", "Relaciones de amistad confirmadas. Clave primaria: friendship_id. Claves foráneas: user_id1, user_id2 → users(user_id). UNIQUE(user_id1, user_id2)."),
        ("hidden_comments", "Registro de comentarios ocultados por usuarios. UNIQUE(user_id, comment_id)."),
        ("user_update_log", "Auditoría de cambios de perfil. Clave primaria: log_id. Clave foránea: user_id → users(user_id). Campos: old_full_name, new_full_name, old_biography, new_biography, change_date."),
    ]

    tbl_data = [["Tabla", "Descripción"]]
    for name, desc in tables:
        tbl_data.append([name, desc])
    story.append(make_table(tbl_data, [1.3*inch, 5.2*inch]))

    story.append(Spacer(1, 0.2*inch))

    story.append(heading("9.2 Procedimientos Almacenados", 2))
    story.append(body("""El sistema utiliza 26 Stored Procedures para encapsular la lógica de negocio en la capa de base de datos, garantizando atomicidad, seguridad y rendimiento:"""))

    sps = [
        ["Procedimiento", "Descripción"],
        ["sp_register_user", "Registra un nuevo usuario con validación de correo duplicado."],
        ["sp_login_user", "Recupera datos de usuario por correo para autenticación."],
        ["sp_create_post", "Crea una nueva publicación y retorna su ID."],
        ["sp_create_comment", "Crea un comentario y retorna su ID."],
        ["sp_get_comments_by_post", "Obtiene comentarios de una publicación con datos del autor."],
        ["sp_delete_comment", "Soft delete de un comentario (active=0)."],
        ["sp_get_comment_count", "Conteo de comentarios activos de un post."],
        ["sp_get_comment_by_id", "Obtiene un comentario específico por ID."],
        ["sp_create_reply", "Crea una respuesta a un comentario."],
        ["sp_get_replies_by_comment", "Obtiene respuestas de un comentario con datos del autor."],
        ["sp_delete_reply", "Soft delete de una respuesta."],
        ["sp_get_reply_count", "Conteo de respuestas activas de un comentario."],
        ["sp_get_reply_by_id", "Obtiene una respuesta específica por ID."],
        ["sp_add_like", "Agrega un like a una publicación (INSERT IGNORE)."],
        ["sp_remove_like", "Elimina un like de una publicación."],
        ["sp_get_like_count", "Conteo de likes de una publicación."],
        ["sp_has_liked", "Verifica si un usuario dio like a una publicación."],
        ["sp_get_user_likes", "Obtiene todos los likes de un usuario."],
        ["sp_get_post_likers", "Obtiene usuarios que dieron like a un post."],
        ["sp_add_comment_like", "Agrega like a un comentario."],
        ["sp_remove_comment_like", "Elimina like de comentario."],
        ["sp_get_comment_like_count", "Conteo de likes de un comentario."],
        ["sp_has_comment_liked", "Verifica like en comentario."],
        ["sp_add_reply_like", "Agrega like a una respuesta."],
        ["sp_remove_reply_like", "Elimina like de respuesta."],
        ["sp_get_reply_like_count", "Conteo de likes de una respuesta."],
        ["sp_has_reply_liked", "Verifica like en respuesta."],
    ]
    story.append(make_table(sps, [2.4*inch, 4.1*inch]))

    story.append(Spacer(1, 0.15*inch))

    story.append(heading("9.3 Vistas", 2))
    story.append(body("<b>v_posts_stats:</b> Vista que combina la tabla posts con users, conteo de likes y conteo de comentarios activos para renderizar eficientemente el feed principal. Incluye: post_id, user_id, content, image, created_at, author_name, author_picture, author_email, likes_count, comments_count."))

    story.append(Spacer(1, 0.1*inch))

    story.append(heading("9.4 Triggers", 2))
    story.append(body("<b>trg_user_update_log:</b> Trigger BEFORE UPDATE en la tabla users que registra automáticamente los cambios de nombre y biografía en la tabla user_update_log para fines de auditoría y trazabilidad."))

    story.append(PageBreak())


def build_sequence_diagrams(story):
    """Section 10: Diagramas de Secuencia"""
    story.append(heading("10. DIAGRAMAS DE SECUENCIA", 1))
    story.append(body("""A continuación se describen los diagramas de secuencia de los principales flujos del sistema, siguiendo el patrón de comunicación entre el Cliente (Frontend JavaScript), el Enrutador PHP, el Controlador, el Modelo y la Base de Datos MySQL."""))

    diagrams = [
        ("10.1 Registro de Usuario (Usuario Regular)",
         ["1. Usuario accede a /register →",
          "2. Sistema muestra formulario de registro →",
          "3. Usuario completa datos y envía →",
          "4. Cliente (JS) valida campos requeridos →",
          "5. Fetch POST /register con datos JSON →",
          "6. Router → AuthController::register() →",
          "7. Controller sanitiza inputs →",
          "8. Controller → UserModel::registerUser() →",
          "9. Model ejecuta sp_register_user →",
          "10. MySQL valida y registra usuario →",
          "11. Model retorna resultado →",
          "12. Controller retorna JSON response →",
          "13. Cliente muestra confirmación y redirige a /login"]),

        ("10.2 Crear Publicación (Usuario Regular)",
         ["1. Usuario accede a /addPost →",
          "2. Sistema muestra formulario de creación →",
          "3. Usuario ingresa texto y/o selecciona imagen →",
          "4. Cliente valida tipo de archivo y tamaño →",
          "5. Fetch POST /posts con FormData →",
          "6. Router → PostController::store() →",
          "7. Controller sanitiza texto →",
          "8. Controller procesa upload de imagen →",
          "9. Controller → PostModel::createPost() →",
          "10. Model ejecuta sp_create_post →",
          "11. MySQL inserta registro en tabla posts →",
          "12. Model retorna post_id →",
          "13. Controller retorna JSON success →",
          "14. Cliente redirige a /posts (feed)"]),

        ("10.3 Dar Me Gusta a Publicación (Usuario Regular)",
         ["1. Usuario hace clic en icono de corazón en un post →",
          "2. Cliente (JS) captura evento click →",
          "3. Cliente verifica estado actual (liked/unliked) →",
          "4. Si no ha dado like: Fetch POST /posts/:id/like →",
          "5. Si ya dio like: Fetch DELETE /posts/:id/like →",
          "6. Router → PostController::like() / unlike() →",
          "7. Controller verifica autenticación →",
          "8. Controller → LikeModel::addLike() / removeLike() →",
          "9. Model ejecuta sp_add_like / sp_remove_like →",
          "10. MySQL INSERT IGNORE o DELETE →",
          "11. Model retorna affected_rows →",
          "12. Controller retorna JSON con nuevo conteo de likes →",
          "13. Cliente actualiza contador e icono sin recargar página"]),

        ("10.4 Enviar Solicitud de Amistad (Usuario Regular)",
         ["1. Usuario busca perfil → clic en 'Enviar Solicitud' →",
          "2. Cliente (JS) captura evento →",
          "3. Fetch POST /friend/request/:id →",
          "4. Router → FriendController::sendRequestById() →",
          "5. Controller verifica autenticación →",
          "6. Controller → FriendModel::sendRequest() →",
          "7. Model valida que no exista solicitud previa →",
          "8. Model INSERT en friend_requests (status='pending') →",
          "9. Model retorna resultado →",
          "10. Controller retorna JSON status →",
          "11. Cliente actualiza botón a 'Solicitud Enviada'"]),

        ("10.5 Dashboard Administrativo (Administrador)",
         ["1. Admin inicia sesión → redirigido a /dashboard →",
          "2. Router verifica rol 'admin' → requireAdmin() →",
          "3. Sistema carga vista admin/dashboard.php →",
          "4. Cliente (JS) ejecuta múltiples Fetch en paralelo →",
          "5. GET /admin/stats/summary → AdminController →",
          "6. AdminModel::getSummaryStats() consulta BD →",
          "7. Retorna total users, posts, comments, likes, friends →",
          "8. GET /admin/stats/activity-timeline → →",
          "9. AdminModel::getActivityTimeline() consulta BD →",
          "10. GET /admin/stats/engagement-breakdown → →",
          "11. GET /admin/stats/user-growth → →",
          "12. Cliente renderiza gráficas Chart.js →",
          "13. Cliente llena tablas de rankings →"]),

        ("10.6 Generar Reporte PDF (Administrador)",
         ["1. Admin hace clic en 'Descargar Reporte PDF' →",
          "2. Cliente (JS) abre ventana a /admin/stats/pdf →",
          "3. Router → PdfController::downloadStatsPdf() →",
          "4. Controller → AdminModel::getUsersWithMostPosts() →",
          "5. Controller → AdminModel::getUsersWithMostFriends() →",
          "6. Controller → AdminModel::getPostsWithMostComments() →",
          "7. Controller → AdminModel::getPostsWithMostLikes() →",
          "8. Controller instancia TCPDF y construye documento →",
          "9. Controller establece headers Content-Type: application/pdf →",
          "10. TCPDF genera PDF con tablas y datos →",
          "11. Navegador recibe PDF y lo descarga"]),
    ]

    for title, steps in diagrams:
        story.append(heading(title, 2))
        for step in steps:
            story.append(body(step))
        story.append(Spacer(1, 0.15*inch))

    story.append(PageBreak())


def build_class_diagrams(story):
    """Section 11: Diagramas de Clases"""
    story.append(heading("11. DIAGRAMAS DE CLASES", 1))
    story.append(body("""Los diagramas de clases representan la estructura estática del sistema UNIRED, modelando las entidades principales y sus relaciones."""))

    story.append(heading("11.1 Estructura General MVC", 2))
    story.append(body("""El sistema sigue el patrón de arquitectura MVC (Modelo-Vista-Controlador) con las siguientes clases principales:"""))
    story.append(Spacer(1, 0.1*inch))

    story.append(bold_body("Capa de Controladores (app/Controllers/):"))
    story.append(body("• <b>AuthController:</b> Gestiona registro, inicio de sesión y cierre de sesión. Métodos: register(), login(), logout()."))
    story.append(body("• <b>PostController:</b> CRUD de publicaciones, likes y comentarios. Métodos: store(), update(), destroy(), like(), unlike(), addComment(), deleteComment(), getComments(), addReply(), deleteReply(), getReplies(), likeComment(), likeReply()."))
    story.append(body("• <b>UserController:</b> Gestión de perfiles de usuario. Métodos: show(), edit(), update(), deleteAccount()."))
    story.append(body("• <b>FriendController:</b> Gestión de relaciones de amistad. Métodos: sendRequest(), sendRequestById(), acceptRequest(), rejectRequest(), cancelRequest(), removeFriend(), getFriends(), getPendingRequests(), getSentRequests(), getSuggestions(), getStatus()."))
    story.append(body("• <b>AdminController:</b> Endpoints de estadísticas para el dashboard. 9 métodos: getSummaryStats(), getActivityTimeline(), getEngagementBreakdown(), getUserGrowth(), getUserActivitySplit(), getPostsByDayOfWeek(), getPostImageRatio(), getTopEngagedUsers(), y 4 métodos de ranking."))
    story.append(body("• <b>PdfController:</b> Generación de reportes PDF. Método: downloadStatsPdf()."))

    story.append(Spacer(1, 0.1*inch))

    story.append(bold_body("Capa de Modelos (app/Models/):"))
    story.append(body("• <b>UserModel:</b> Operaciones CRUD sobre tabla users. Utiliza sp_register_user y sp_login_user."))
    story.append(body("• <b>PostModel:</b> Operaciones sobre tabla posts. Métodos: createPost(), getAllPosts(), getPostById(), updatePost(), deletePost(), getPostsByUserId()."))
    story.append(body("• <b>CommentModel:</b> Operaciones sobre tabla comments. Métodos: createComment(), getCommentsByPost(), deleteComment(), getCommentCount(), getCommentById()."))
    story.append(body("• <b>ReplyModel:</b> Operaciones sobre tabla replies. Métodos: createReply(), getRepliesByComment(), deleteReply(), getReplyCount(), getReplyById()."))
    story.append(body("• <b>LikeModel:</b> Gestión de likes en publicaciones. Métodos: addLike(), removeLike(), getLikeCount(), hasLiked(), getUserLikes(), getPostLikers()."))
    story.append(body("• <b>CommentLikeModel:</b> Gestión de likes en comentarios. Métodos: addLike(), removeLike(), getLikeCount(), hasLiked()."))
    story.append(body("• <b>ReplyLikeModel:</b> Gestión de likes en respuestas. Métodos: addLike(), removeLike(), getLikeCount(), hasLiked()."))
    story.append(body("• <b>FriendModel:</b> Gestión de amistades. Métodos: getFriends(), getPendingRequests(), getSuggestions(), sendRequest(), acceptRequest(), rejectRequest(), removeFriend(), getFriendshipStatus()."))
    story.append(body("• <b>AdminModel:</b> Consultas estadísticas agregadas. Múltiples métodos para dashboard administrativo."))

    story.append(Spacer(1, 0.1*inch))

    story.append(bold_body("Capa de Componentes (app/Components/):"))
    story.append(body("• <b>Alert:</b> Renderiza mensajes flash (éxito, error, info) con estilos condicionales."))
    story.append(body("• <b>Post:</b> Renderiza una publicación completa incluyendo cabecera, contenido, imagen, botones de interacción y sección de comentarios."))
    story.append(body("• <b>FriendCard:</b> Renderiza tarjeta de amigo/sugerencia con botones contextuales según el estado de la relación."))
    story.append(body("• <b>Profile:</b> Renderiza cabecera de perfil con estadísticas, foto y botones de acción."))

    story.append(Spacer(1, 0.2*inch))

    story.append(heading("11.2 Relaciones entre Clases", 2))

    relationships = [
        ["Relación", "Descripción"],
        ["Usuario → Publicación (1:N)", "Un usuario puede crear múltiples publicaciones. FK user_id en posts."],
        ["Publicación → Comentario (1:N)", "Una publicación puede tener múltiples comentarios. FK post_id en comments."],
        ["Comentario → Respuesta (1:N)", "Un comentario puede tener múltiples respuestas. FK comment_id en replies."],
        ["Usuario → Like (N:M)", "Relación muchos a muchos entre usuarios y publicaciones vía tabla likes. UNIQUE(post_id, user_id)."],
        ["Usuario → Amigo (N:M)", "Relación muchos a muchos entre usuarios vía tabla friends. UNIQUE(user_id1, user_id2)."],
        ["Usuario → Solicitud (N:M)", "Relación bidireccional vía friend_requests con estados (pending/accepted/rejected/cancelled)."],
        ["Usuario → Auditoría (1:N)", "Trigger BEFORE UPDATE registra cambios de perfil en user_update_log."],
    ]
    story.append(make_table(relationships, [2.5*inch, 4*inch]))

    story.append(PageBreak())


def build_testing(story):
    """Section 12: Pruebas"""
    story.append(heading("12. PRUEBAS", 1))
    story.append(body("""Para garantizar la calidad del software, se realizaron pruebas de caja blanca y caja negra, documentando los casos de prueba y sus resultados."""))

    story.append(heading("12.1 Pruebas de Caja Blanca", 2))
    story.append(body("""Las pruebas de caja blanca se enfocaron en verificar la lógica interna del código, específicamente en los Stored Procedures y controladores."""))

    story.append(heading("12.1.1 Caso de Prueba CB-01: Registro de Usuario", 3))
    cb01 = [
        ["Campo", "Descripción"],
        ["ID Caso de Prueba", "CB-01"],
        ["Módulo", "Autenticación - Registro de Usuario"],
        ["Objetivo", "Verificar que sp_register_user detecte correctamente correos duplicados y registre usuarios válidos."],
        ["Entradas", "Caso 1: correo nuevo (test@uabcs.mx, password: Test123!). Caso 2: correo existente (admin@gmail.com)."],
        ["Resultado Esperado", "Caso 1: usuario registrado exitosamente. Caso 2: error 'El correo ya está registrado'."],
        ["Resultado Obtenido", "Caso 1: registro exitoso, fila insertada en users. Caso 2: SIGNAL SQLSTATE 45000 lanzada correctamente."],
        ["Estado", "APROBADO ✓"],
    ]
    story.append(make_ieee_spec_table(cb01))
    story.append(Spacer(1, 0.15*inch))

    story.append(heading("12.1.2 Caso de Prueba CB-02: Sistema de Likes", 3))
    cb02 = [
        ["Campo", "Descripción"],
        ["ID Caso de Prueba", "CB-02"],
        ["Módulo", "Publicaciones - Sistema de Likes"],
        ["Objetivo", "Verificar que sp_add_like use INSERT IGNORE correctamente y sp_remove_like elimine el registro."],
        ["Entradas", "post_id=1, user_id=2. Ejecutar sp_add_like dos veces con los mismos parámetros."],
        ["Resultado Esperado", "Primera ejecución: 1 fila afectada. Segunda ejecución: 0 filas afectadas (ignorada)."],
        ["Resultado Obtenido", "Primera: affected_rows=1. Segunda: affected_rows=0. Sin duplicados. Confirmado con SELECT COUNT(*)."],
        ["Estado", "APROBADO ✓"],
    ]
    story.append(make_ieee_spec_table(cb02))
    story.append(Spacer(1, 0.15*inch))

    story.append(heading("12.2 Pruebas de Caja Negra", 2))
    story.append(body("""Las pruebas de caja negra verifican el comportamiento del sistema desde la perspectiva del usuario final, sin conocer la implementación interna."""))

    story.append(heading("12.2.1 Pruebas de Aceptación", 3))
    story.append(body("""Se verificaron los criterios de aceptación definidos en la sección de validación de requerimientos para cada requerimiento funcional:"""))

    acceptance = [
        ["Requerimiento", "Criterio", "Resultado"],
        ["RF-01 Registro", "Usuario puede registrarse con datos válidos", "APROBADO ✓"],
        ["RF-01 Registro", "Sistema rechaza correo duplicado", "APROBADO ✓"],
        ["RF-02 Login", "Usuario puede iniciar sesión con credenciales correctas", "APROBADO ✓"],
        ["RF-02 Login", "Sistema rechaza credenciales incorrectas", "APROBADO ✓"],
        ["RF-03 Publicación", "Usuario puede crear publicación con texto e imagen", "APROBADO ✓"],
        ["RF-03 Publicación", "Sistema rechaza archivos no permitidos", "APROBADO ✓"],
        ["RF-04 Comentarios", "Comentarios se cargan con paginación (3 en 3)", "APROBADO ✓"],
        ["RF-05 Amigos", "Flujo completo enviar-aceptar-rechazar funciona", "APROBADO ✓"],
        ["RF-06 Perfil", "Usuario puede editar nombre, bio y foto", "APROBADO ✓"],
        ["RF-07 Dashboard", "Admin ve estadísticas y gráficas correctamente", "APROBADO ✓"],
        ["RF-08 PDF", "Reporte PDF se genera y descarga correctamente", "APROBADO ✓"],
        ["RF-09 Eliminar Post", "Solo el autor puede eliminar su publicación", "APROBADO ✓"],
        ["RF-15 Eliminar Cuenta", "Eliminación en cascada de todos los datos", "APROBADO ✓"],
    ]
    story.append(make_table(acceptance, [1.7*inch, 3*inch, 1.8*inch]))
    story.append(Spacer(1, 0.15*inch))

    story.append(heading("12.2.2 Pruebas de Usabilidad de Interfaz", 3))
    story.append(body("""Se realizaron pruebas de usabilidad verificando los siguientes aspectos:"""))
    story.append(body("• <b>Diseño Responsive:</b> Verificado en resoluciones 320px (móvil), 768px (tablet) y 1920px (desktop). Sidebar funciona como drawer en móvil."))
    story.append(body("• <b>Navegación:</b> Todos los enlaces de la sidebar dirigen a las vistas correctas. Cerrar sesión limpia la sesión adecuadamente."))
    story.append(body("• <b>Modales de Confirmación:</b> Acciones críticas (eliminar cuenta, eliminar publicación, eliminar amigo) muestran modal de confirmación antes de ejecutarse."))
    story.append(body("• <b>Feedback Visual:</b> Los botones cambian de estado al hacer hover. Las operaciones asíncronas muestran indicadores de carga. Los mensajes flash aparecen después de operaciones exitosas o fallidas."))
    story.append(body("• <b>Accesibilidad:</b> Contraste adecuado entre texto y fondo. Tamaños de fuente legibles. Formularios con etiquetas claras."))
    story.append(body("<b>Resultado:</b> APROBADO ✓ — Todas las pruebas de usabilidad fueron superadas satisfactoriamente."))

    story.append(Spacer(1, 0.15*inch))

    story.append(heading("12.2.3 Pruebas de Seguridad", 3))
    story.append(body("""Pruebas de seguridad realizadas como parte de caja negra (libre elección):"""))
    story.append(body("• <b>Inyección SQL:</b> Verificado que todas las consultas usan PDO con prepared statements. No se pudo inyectar SQL malicioso."))
    story.append(body("• <b>XSS (Cross-Site Scripting):</b> Verificado que todas las salidas de usuario pasan por htmlspecialchars() o safe_output(). Scripts maliciosos no se ejecutan."))
    story.append(body("• <b>CSRF:</b> Verificado que operaciones de cambio de estado (POST, PUT, DELETE) requieren sesión activa."))
    story.append(body("• <b>Control de Acceso:</b> Verificado que usuarios sin autenticación no pueden acceder a rutas protegidas. Usuarios regulares no pueden acceder al dashboard administrativo."))
    story.append(body("• <b>Subida de Archivos:</b> Verificado que solo se aceptan imágenes (JPEG, PNG, GIF) y el tamaño máximo es de 5 MB. Archivos PHP renombrados como .jpg no se ejecutan."))
    story.append(body("<b>Resultado:</b> APROBADO ✓ — El sistema pasó todas las pruebas de seguridad sin vulnerabilidades detectadas."))

    story.append(PageBreak())


def build_deployment(story):
    """Section 13: Plan de Implantación"""
    story.append(heading("13. PLAN DE IMPLANTACIÓN", 1))
    story.append(body("""El plan de implantación considera las 5 acciones necesarias para poner el sistema en funcionamiento en el entorno de producción."""))

    actions = [
        ("13.1 Instalación de Software",
         ["1. Configurar servidor web Apache con mod_rewrite habilitado.",
          "2. Instalar PHP 7.4+ con extensiones: PDO, MySQL, mbstring, fileinfo, gd.",
          "3. Instalar Composer para gestión de dependencias.",
          "4. Clonar repositorio del proyecto en el directorio raíz del servidor web.",
          "5. Ejecutar 'composer install' para instalar dependencias (vlucas/phpdotenv, TCPDF).",
          "6. Configurar archivo .env con credenciales de base de datos.",
          "7. Configurar permisos de escritura en directorios assets/imagesProfile/ y assets/imagesPosts/."]),
        ("13.2 Instalación de Hardware y Telecomunicaciones",
         ["1. Contratar servicio de hosting en la nube (Hostinger).",
          "2. Configurar dominio (darksalmon-jellyfish-884197.hostingersite.com).",
          "3. Configurar certificado SSL para HTTPS.",
          "4. Verificar conectividad de red y ancho de banda adecuado.",
          "5. Configurar backups automáticos de base de datos (diarios)."]),
        ("13.3 Carga de Datos",
         ["1. Ejecutar script database/unired_db.sql para crear estructura de base de datos.",
          "2. Verificar creación de 11 tablas, 26 Stored Procedures, 1 Trigger y 1 Vista.",
          "3. Ejecutar script database/seed.php para poblar datos de prueba:",
          "   - 25 usuarios de prueba",
          "   - 55 publicaciones",
          "   - 150 comentarios",
          "   - 80 respuestas",
          "   - 300 likes en publicaciones",
          "   - 100 likes en comentarios",
          "   - 60 likes en respuestas",
          "   - 40 relaciones de amistad",
          "4. Verificar integridad de datos con consultas de validación.",
          "5. Crear cuenta de administrador: admin@gmail.com / Admin123?."]),
        ("13.4 Capacitación",
         ["1. Sesión de capacitación para administradores del sistema (1 hora):",
          "   - Acceso al dashboard administrativo.",
          "   - Interpretación de gráficas y métricas.",
          "   - Generación de reportes PDF.",
          "2. Sesión de capacitación para usuarios finales (estudiantes y docentes) (1 hora):",
          "   - Registro e inicio de sesión.",
          "   - Creación de publicaciones con texto e imágenes.",
          "   - Sistema de comentarios y respuestas.",
          "   - Gestión de amistades (enviar, aceptar, rechazar).",
          "   - Edición de perfil y configuración de privacidad.",
          "3. Entrega de manual de usuario digital.",
          "4. Video tutorial de uso del sistema."]),
        ("13.5 Conversión",
         ["1. Estrategia de adopción gradual (no Big Bang):",
          "   - Fase 1 (Semana 1): Lanzamiento con grupo piloto de 50 estudiantes.",
          "   - Fase 2 (Semana 2): Apertura a toda la comunidad universitaria.",
          "   - Fase 3 (Semana 3+): Operación normal con monitoreo continuo.",
          "2. Período de operación paralela opcional (no aplica — no hay sistema previo).",
          "3. Monitoreo de métricas de adopción: nuevos registros diarios, publicaciones creadas, interacciones.",
          "4. Canal de soporte para reportar bugs o sugerencias.",
          "5. Plan de mantenimiento: actualizaciones mensuales de seguridad y dependencias."]),
    ]

    for title, items in actions:
        story.append(heading(title, 2))
        for item in items:
            story.append(body(item))
        story.append(Spacer(1, 0.1*inch))

    story.append(PageBreak())


def build_user_manual(story):
    """Section 14: Manual de Usuario"""
    story.append(heading("14. MANUAL DE USUARIO", 1))
    story.append(body("""Este manual describe los pasos necesarios para utilizar el Sistema Integral de Red Social Universitaria UNIRED."""))

    story.append(heading("14.1 Instalación (No aplica para usuarios finales)", 2))
    story.append(body("""UNIRED es una aplicación web. Los usuarios finales no requieren instalar ningún software adicional. Solo necesitan un navegador web actualizado (Chrome, Firefox, Safari, Edge) y conexión a internet. La URL de acceso es: https://darksalmon-jellyfish-884197.hostingersite.com/"""))

    story.append(heading("14.2 Uso del Sistema", 2))

    story.append(heading("14.2.1 Registro de Usuario", 3))
    story.append(body("1. Acceder a la URL del sistema."))
    story.append(body("2. Hacer clic en 'Registrarse' o navegar a /register."))
    story.append(body("3. Completar el formulario con: nombre completo, correo electrónico y contraseña."))
    story.append(body("4. Hacer clic en 'Registrarse'."))
    story.append(body("5. Si el registro es exitoso, será redirigido a la página de inicio de sesión."))

    story.append(heading("14.2.2 Inicio de Sesión", 3))
    story.append(body("1. Navegar a /login."))
    story.append(body("2. Ingresar correo electrónico y contraseña."))
    story.append(body("3. Hacer clic en 'Iniciar Sesión'."))
    story.append(body("4. El sistema redirigirá al feed de publicaciones (rol user) o al dashboard (rol admin)."))

    story.append(heading("14.2.3 Navegación Principal", 3))
    story.append(body("La barra lateral izquierda (sidebar) contiene los siguientes enlaces:"))
    story.append(body("• <b>Inicio (Feed):</b> Muestra las publicaciones de todos los usuarios."))
    story.append(body("• <b>Amigos:</b> Gestiona lista de amigos, solicitudes pendientes y búsqueda de usuarios."))
    story.append(body("• <b>Mi Perfil:</b> Visualiza tu perfil y tus publicaciones."))
    story.append(body("• <b>Agregar Post:</b> Crea una nueva publicación con texto e imagen opcional."))
    story.append(body("• <b>Editar Perfil:</b> Modifica tu nombre, biografía y foto de perfil."))
    story.append(body("• <b>Cerrar Sesión:</b> Finaliza la sesión actual."))
    story.append(body("En dispositivos móviles, la sidebar se oculta y se muestra al presionar el botón de menú."))

    story.append(heading("14.2.4 Crear una Publicación", 3))
    story.append(body("1. Hacer clic en 'Agregar Post' en la sidebar."))
    story.append(body("2. Escribir el contenido en el campo de texto."))
    story.append(body("3. Opcionalmente, seleccionar una imagen (JPEG, PNG o GIF, máximo 5 MB)."))
    story.append(body("4. Hacer clic en 'Publicar'."))
    story.append(body("5. La publicación aparecerá en el feed general."))

    story.append(heading("14.2.5 Interactuar con Publicaciones", 3))
    story.append(body("• <b>Me Gusta:</b> Haz clic en el icono de corazón para dar like. Vuelve a hacer clic para quitarlo."))
    story.append(body("• <b>Comentarios:</b> Haz clic en 'Comentarios' para ver los comentarios existentes. Escribe en el campo de texto y presiona Enter o el botón de envío para comentar."))
    story.append(body("• <b>Responder:</b> Dentro de un comentario, haz clic en 'Responder' para agregar una respuesta."))
    story.append(body("• <b>Ver Imagen:</b> Haz clic en una imagen para verla en pantalla completa. Haz clic en la X para cerrar."))
    story.append(body("• <b>Editar/Eliminar:</b> Si eres el autor de la publicación, verás un menú de opciones (tres puntos) para editar o eliminar."))

    story.append(heading("14.2.6 Gestionar Amigos", 3))
    story.append(body("1. Navegar a la sección 'Amigos'."))
    story.append(body("2. Usar la barra de búsqueda para encontrar usuarios por nombre."))
    story.append(body("3. Hacer clic en 'Enviar Solicitud' para conectar con otro usuario."))
    story.append(body("4. En la pestaña 'Solicitudes', ver las solicitudes entrantes y aceptarlas o rechazarlas."))
    story.append(body("5. En la pestaña 'Mis Amigos', ver la lista de amigos y opción para eliminar."))

    story.append(heading("14.2.7 Editar Perfil", 3))
    story.append(body("1. Hacer clic en 'Editar Perfil' en la sidebar."))
    story.append(body("2. Modificar nombre completo y/o biografía."))
    story.append(body("3. Para cambiar la foto, hacer clic en 'Seleccionar archivo' y elegir una imagen."))
    story.append(body("4. Hacer clic en 'Guardar Cambios'."))
    story.append(body("5. Los cambios se reflejarán inmediatamente en el perfil."))

    story.append(heading("14.2.8 Dashboard Administrativo", 3))
    story.append(body("Disponible solo para usuarios con rol 'admin'. Al iniciar sesión como administrador (admin@gmail.com):"))
    story.append(body("• Ver tarjetas de resumen con totales de usuarios, publicaciones, comentarios, likes y amistades."))
    story.append(body("• Explorar gráficas interactivas de actividad, engagement, crecimiento de usuarios, etc."))
    story.append(body("• Consultar rankings de usuarios con más publicaciones y amigos."))
    story.append(body("• Consultar rankings de publicaciones con más comentarios y likes."))
    story.append(body("• Hacer clic en 'Descargar Reporte PDF' para obtener un documento con estadísticas."))

    story.append(Spacer(1, 0.2*inch))

    story.append(heading("14.3 Solución de Problemas", 2))

    problems = [
        ["Problema", "Solución"],
        ["No puedo iniciar sesión", "Verificar que el correo y contraseña sean correctos. Si olvidó la contraseña, contactar al administrador."],
        ["No puedo subir una imagen", "Verificar que la imagen sea JPEG, PNG o GIF y no exceda 5 MB."],
        ["No veo mis publicaciones", "Verificar que ha iniciado sesión. Si el problema persiste, cerrar sesión y volver a iniciar."],
        ["El sistema está lento", "Verificar la conexión a internet. Si el problema persiste, contactar al administrador del sistema."],
        ["Error al eliminar cuenta", "Asegurarse de haber iniciado sesión. La eliminación es permanente y elimina todos los datos asociados."],
        ["La sidebar no se muestra en móvil", "Presionar el botón de menú (tres líneas horizontales) en la esquina superior para desplegar la sidebar."],
    ]
    story.append(make_table(problems, [1.8*inch, 4.7*inch]))

    story.append(Spacer(1, 0.2*inch))

    story.append(heading("14.4 Desinstalación (No aplica para usuarios finales)", 2))
    story.append(body("""UNIRED es una aplicación web, por lo que los usuarios finales no requieren desinstalar nada. Para dejar de usar el sistema, simplemente dejen de acceder a la URL. Si desean eliminar sus datos, pueden usar la opción 'Eliminar Cuenta' en la sección de edición de perfil, lo cual eliminará permanentemente toda su información del sistema."""))

    story.append(Spacer(1, 0.3*inch))
    story.append(hr())
    story.append(Spacer(1, 0.1*inch))
    story.append(Paragraph("FIN DEL DOCUMENTO", center_style))
    story.append(Spacer(1, 0.1*inch))
    story.append(hr())


# ── Page Number Callback ───────────────────────────────────────
def add_page_number(canvas, doc):
    page_num = canvas.getPageNumber()
    if page_num > 1:
        text = f"UNIRED — Documentación Técnica    |    Página {page_num - 1}"
        canvas.saveState()
        canvas.setFont('Times-Roman', 8)
        canvas.drawCentredString(PAGE_W / 2, 0.5 * inch, text)
        canvas.restoreState()


# ── Main Document Builder ─────────────────────────────────────
def build_document():
    doc = SimpleDocTemplate(
        OUTPUT,
        pagesize=letter,
        topMargin=0.8*inch,
        bottomMargin=0.8*inch,
        leftMargin=1*inch,
        rightMargin=1*inch,
        title="Sistema Integral de Red Social Universitaria - UNIRED",
        author="Gabriel Lauro Hernández, Jose Manuel Orozco Vazquez, David Gonzalez Vargas",
        subject="Documentación Técnica y de Análisis - Ingeniería de Software II",
    )

    story = []

    # Build all sections
    build_cover(story)
    build_toc(story)
    build_feasibility(story)
    build_interviews(story)
    build_requirements_table(story)
    build_functional_requirements(story)
    build_validation(story)
    build_analysis_unit(story)
    build_use_cases(story)
    build_design(story)
    build_database(story)
    build_sequence_diagrams(story)
    build_class_diagrams(story)
    build_testing(story)
    build_deployment(story)
    build_user_manual(story)

    # Build PDF with page numbers
    doc.build(story, onFirstPage=add_page_number, onLaterPages=add_page_number)
    print(f"PDF generated successfully: {OUTPUT}")
    print(f"File size: {os.path.getsize(OUTPUT) / 1024:.1f} KB")


if __name__ == "__main__":
    build_document()
