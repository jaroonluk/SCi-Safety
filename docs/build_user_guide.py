#!/usr/bin/env python3
"""Generate SCi-Safety Thai user guide PDF with illustrations."""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont
from reportlab.lib.colors import Color, HexColor, white
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm, mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    Image as RLImage,
    KeepTogether,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)

ROOT = Path(__file__).resolve().parent
FONT_REG = ROOT / "fonts" / "Sarabun-Regular.ttf"
FONT_BOLD = ROOT / "fonts" / "Sarabun-Bold.ttf"
FONT_SEMI = ROOT / "fonts" / "Sarabun-SemiBold.ttf"
IMG_DIR = ROOT / "guide_assets"
OUT_PDF = ROOT / "SCI-Safety-User-Guide-TH.pdf"

BRAND = HexColor("#9f1239")
BRAND_DEEP = HexColor("#881337")
INK = HexColor("#1c1917")
MUTED = HexColor("#57534e")
LINE = HexColor("#f3e4e1")
SAND = HexColor("#fff1f2")
CARD = HexColor("#ffffff")
SUCCESS = HexColor("#047857")
WARN = HexColor("#b45309")
INFO = HexColor("#1d4ed8")


def ensure_fonts() -> None:
    pdfmetrics.registerFont(TTFont("Sarabun", str(FONT_REG)))
    pdfmetrics.registerFont(TTFont("Sarabun-Bold", str(FONT_BOLD)))
    if FONT_SEMI.exists():
        pdfmetrics.registerFont(TTFont("Sarabun-Semi", str(FONT_SEMI)))
    else:
        pdfmetrics.registerFont(TTFont("Sarabun-Semi", str(FONT_BOLD)))


def pil_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    path = FONT_BOLD if bold else FONT_REG
    return ImageFont.truetype(str(path), size)


def rounded_rect(draw: ImageDraw.ImageDraw, box, fill, outline=None, radius=18, width=2):
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def save_img(img: Image.Image, name: str) -> Path:
    IMG_DIR.mkdir(parents=True, exist_ok=True)
    path = IMG_DIR / name
    img.save(path, "PNG")
    return path


def draw_flow_overview() -> Path:
    w, h = 1400, 520
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    title = pil_font(34, True)
    body = pil_font(22)
    small = pil_font(18)
    d.text((40, 24), "ภาพรวมเส้นทางคำขอในระบบ SCi-Safety", font=title, fill="#881337")

    steps = [
        ("1", "ผู้ยื่น\nส่งคำขอ", "#9f1239"),
        ("2", "เจ้าหน้าที่\nตรวจภาพ", "#be123c"),
        ("3", "พิจารณา\nตามประเภท", "#b45309"),
        ("4", "อนุญาต\nและนัดหมาย", "#047857"),
        ("5", "ดูภาพ\nในห้องควบคุม", "#1d4ed8"),
        ("6", "ปิดเรื่อง", "#57534e"),
    ]
    box_w, box_h = 170, 120
    gap = 40
    start_x = 40
    y = 160
    for i, (num, label, color) in enumerate(steps):
        x = start_x + i * (box_w + gap)
        rounded_rect(d, (x, y, x + box_w, y + box_h), "#ffffff", color, 20, 3)
        d.ellipse((x + 12, y + 12, x + 48, y + 48), fill=color)
        d.text((x + 24, y + 16), num, font=pil_font(22, True), fill="#ffffff")
        # multiline label
        lines = label.split("\n")
        ty = y + 62
        for line in lines:
            tw = d.textlength(line, font=body)
            d.text((x + (box_w - tw) / 2, ty), line, font=body, fill="#1c1917")
            ty += 26
        if i < len(steps) - 1:
            ax = x + box_w + 8
            ay = y + box_h / 2
            d.polygon([(ax, ay - 8), (ax + 22, ay), (ax, ay + 8)], fill=color)

    note = "หมายเหตุ: บางประเภทเหตุอาจผ่านผู้อำนวยการ / รองคณบดี / PDPA ตามกฎที่ผู้ดูแลระบบกำหนด"
    d.text((40, 360), note, font=small, fill="#57534e")
    d.text((40, 420), "ระบบไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ และไม่มีการบันทึกภาพหน้าจอ", font=small, fill="#9f1239")
    return save_img(img, "flow_overview.png")


def draw_login_steps() -> Path:
    w, h = 1400, 420
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    d.text((40, 24), "เริ่มต้นใช้งาน: เข้าสู่ระบบด้วย Google", font=pil_font(32, True), fill="#881337")
    cards = [
        ("ขั้นที่ 1", "เปิดเว็บไซต์ระบบ", "http://localhost:8088\n(หรือ URL ที่คณะแจ้ง)"),
        ("ขั้นที่ 2", "กดปุ่มเข้าสู่ระบบ", "เลือกบัญชี Google\nของ มข. หรือที่ได้รับอนุญาต"),
        ("ขั้นที่ 3", "ระบบเปิดหน้าหลัก", "เมนูจะตรงกับสิทธิ์\nที่ผู้ดูแลกำหนดให้ท่าน"),
    ]
    for i, (step, title, detail) in enumerate(cards):
        x = 40 + i * 440
        rounded_rect(d, (x, 100, x + 400, 360), "#ffffff", "#f3e4e1", 22, 2)
        rounded_rect(d, (x + 20, 120, x + 150, 160), "#9f1239", None, 14, 0)
        d.text((x + 40, 126), step, font=pil_font(20, True), fill="#ffffff")
        d.text((x + 24, 190), title, font=pil_font(26, True), fill="#1c1917")
        ty = 240
        for line in detail.split("\n"):
            d.text((x + 24, ty), line, font=pil_font(20), fill="#57534e")
            ty += 32
    return save_img(img, "login_steps.png")


def draw_role_cards() -> Path:
    w, h = 1400, 780
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    d.text((40, 24), "สิทธิ์ผู้ใช้ในระบบ (6 บทบาท)", font=pil_font(32, True), fill="#881337")
    roles = [
        ("ผู้ยื่นคำขอ", "ยื่นคำขอ ติดตามสถานะ\nยืนยันนัดหมายดูภาพ", "#9f1239"),
        ("เจ้าหน้าที่ผู้รับผิดชอบคำขอ", "ตรวจคิว บันทึกผลกล้อง\nนัดหมายและปิดเรื่อง", "#be123c"),
        ("ผู้อำนวยการกองบริหารงานคณะ", "พิจารณาคำขอ\nดูรายงาน", "#b45309"),
        ("รองคณบดีฝ่ายบริหาร", "พิจารณาคำขอระดับสูง\nดูรายงาน", "#047857"),
        ("เจ้าหน้าที่ประสานงาน PDPA", "ให้ความเห็นด้านข้อมูล\nส่วนบุคคล ดูแลประกาศ", "#1d4ed8"),
        ("ผู้ดูแลระบบสูงสุด", "กำหนดสิทธิ์ กฎเส้นทาง\nตรวจบันทึกการใช้งาน", "#57534e"),
    ]
    for i, (title, detail, color) in enumerate(roles):
        col = i % 3
        row = i // 3
        x = 40 + col * 440
        y = 100 + row * 320
        rounded_rect(d, (x, y, x + 400, y + 280), "#ffffff", color, 22, 3)
        d.rectangle((x, y, x + 14, y + 280), fill=color)
        d.text((x + 36, y + 28), title, font=pil_font(24, True), fill="#1c1917")
        ty = y + 100
        for line in detail.split("\n"):
            d.text((x + 36, ty), line, font=pil_font(22), fill="#57534e")
            ty += 36
    return save_img(img, "role_cards.png")


def draw_requester_steps() -> Path:
    w, h = 1400, 900
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    d.text((40, 24), "ผู้ยื่นคำขอ: ยื่นคำขอแบบ 3 ขั้น", font=pil_font(32, True), fill="#881337")
    blocks = [
        ("ขั้น 1 แนะนำตัว", ["ตรวจชื่อ-อีเมลจาก Google", "เลือกฐานะที่ยื่น", "กรอกสังกัดและเบอร์ติดต่อ", "กด ถัดไป เล่าเหตุการณ์"]),
        ("ขั้น 2 เกิดอะไรขึ้น", ["เลือกประเภทเหตุการณ์", "ระบุวันเวลาและสถานที่", "เลือกอาคาร ชั้น ห้อง", "เล่าเหตุการณ์แล้วไปขั้นถัดไป"]),
        ("ขั้น 3 ข้อตกลง", ["อ่านประกาศความเป็นส่วนตัว", "ทำเครื่องหมายรับทราบ", "กด ส่งคำขอ", "จำเลขที่คำขอไว้ติดตาม"]),
    ]
    for i, (title, items) in enumerate(blocks):
        x = 40 + i * 440
        rounded_rect(d, (x, 100, x + 400, 820), "#ffffff", "#f3e4e1", 22, 2)
        rounded_rect(d, (x + 20, 120, x + 380, 180), "#9f1239", None, 16, 0)
        tw = d.textlength(title, font=pil_font(24, True))
        d.text((x + (400 - tw) / 2, 138), title, font=pil_font(24, True), fill="#ffffff")
        y = 220
        for n, text in enumerate(items, 1):
            d.ellipse((x + 28, y, x + 68, y + 40), fill="#fff1f2", outline="#9f1239", width=2)
            d.text((x + 40, y + 6), str(n), font=pil_font(20, True), fill="#9f1239")
            d.text((x + 84, y + 6), text, font=pil_font(22), fill="#1c1917")
            y += 90
    return save_img(img, "requester_steps.png")


def draw_officer_steps() -> Path:
    w, h = 1400, 620
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    d.text((40, 24), "เจ้าหน้าที่: ลำดับงานในคิว", font=pil_font(32, True), fill="#881337")
    items = [
        ("เปิดเมนูคิวงาน", "ดูคำขอที่รอตรวจ"),
        ("ตรวจรายละเอียด", "อาคาร เวลา เหตุการณ์"),
        ("บันทึกผลเทคนิค", "พบภาพ / ไม่พบ / ไม่มีกล้อง"),
        ("ส่งพิจารณาหรือนัดหมาย", "ตามผลและกฎเส้นทาง"),
        ("ปิดเรื่องหลังดูภาพ", "บันทึกผลสิ้นสุด"),
    ]
    for i, (title, detail) in enumerate(items):
        y = 100 + i * 95
        rounded_rect(d, (40, y, 1360, y + 80), "#ffffff", "#f3e4e1", 16, 2)
        d.ellipse((60, y + 18, 110, y + 68), fill="#be123c")
        d.text((76, y + 28), str(i + 1), font=pil_font(22, True), fill="#ffffff")
        d.text((140, y + 14), title, font=pil_font(26, True), fill="#1c1917")
        d.text((140, y + 46), detail, font=pil_font(20), fill="#57534e")
    return save_img(img, "officer_steps.png")


def draw_review_steps() -> Path:
    w, h = 1400, 480
    img = Image.new("RGB", (w, h), "#fdf8f6")
    d = ImageDraw.Draw(img)
    d.text((40, 24), "ผู้พิจารณา: เห็นชอบ / ขอข้อมูลเพิ่ม / ไม่เห็นชอบ", font=pil_font(30, True), fill="#881337")
    cards = [
        ("เห็นชอบ", "อนุญาตให้ดำเนินการต่อ\nหรือให้นัดดูภาพ", "#047857"),
        ("ขอข้อมูลเพิ่ม", "ส่งกลับให้ผู้ยื่น\nหรือเจ้าหน้าที่เติมข้อมูล", "#b45309"),
        ("ไม่เห็นชอบ", "ยุติคำขอพร้อมเหตุผล\nผู้ยื่นเห็นสถานะได้", "#be123c"),
    ]
    for i, (title, detail, color) in enumerate(cards):
        x = 40 + i * 440
        rounded_rect(d, (x, 110, x + 400, 400), "#ffffff", color, 22, 3)
        d.text((x + 30, 150), title, font=pil_font(30, True), fill=color)
        ty = 230
        for line in detail.split("\n"):
            d.text((x + 30, ty), line, font=pil_font(22), fill="#1c1917")
            ty += 36
    return save_img(img, "review_steps.png")


def styles():
    base = getSampleStyleSheet()
    return {
        "h1": ParagraphStyle(
            "H1TH", parent=base["Heading1"], fontName="Sarabun-Bold", fontSize=22,
            textColor=BRAND_DEEP, spaceAfter=10, spaceBefore=6, leading=30,
        ),
        "h2": ParagraphStyle(
            "H2TH", parent=base["Heading2"], fontName="Sarabun-Bold", fontSize=16,
            textColor=BRAND, spaceAfter=8, spaceBefore=14, leading=24,
        ),
        "h3": ParagraphStyle(
            "H3TH", parent=base["Heading3"], fontName="Sarabun-Semi", fontSize=13.5,
            textColor=INK, spaceAfter=6, spaceBefore=10, leading=20,
        ),
        "body": ParagraphStyle(
            "BodyTH", parent=base["Normal"], fontName="Sarabun", fontSize=12,
            textColor=INK, leading=20, spaceAfter=6,
        ),
        "muted": ParagraphStyle(
            "MutedTH", parent=base["Normal"], fontName="Sarabun", fontSize=11,
            textColor=MUTED, leading=18, spaceAfter=4,
        ),
        "bullet": ParagraphStyle(
            "BulletTH", parent=base["Normal"], fontName="Sarabun", fontSize=12,
            textColor=INK, leading=19, leftIndent=8,
        ),
        "cover_title": ParagraphStyle(
            "CoverTitle", parent=base["Title"], fontName="Sarabun-Bold", fontSize=28,
            textColor=white, alignment=TA_CENTER, leading=38,
        ),
        "cover_sub": ParagraphStyle(
            "CoverSub", parent=base["Normal"], fontName="Sarabun", fontSize=14,
            textColor=white, alignment=TA_CENTER, leading=22,
        ),
        "center": ParagraphStyle(
            "CenterTH", parent=base["Normal"], fontName="Sarabun", fontSize=12,
            textColor=INK, alignment=TA_CENTER, leading=20,
        ),
    }


def bullets(items: list[str], style):
    # Use plain text markers so every PDF reader shows Thai correctly
    # without depending on symbol fonts/icons.
    return [Paragraph(f"{idx}. {item}", style) for idx, item in enumerate(items, 1)]


def figure(path: Path, width=17 * cm, caption: str | None = None, s=None):
    img = RLImage(str(path), width=width, height=width * (Image.open(path).height / Image.open(path).width))
    parts = [img]
    if caption and s:
        parts.append(Paragraph(caption, s["muted"]))
    parts.append(Spacer(1, 8))
    return KeepTogether(parts)


def step_table(rows: list[tuple[str, str]], s) -> Table:
    data = [[Paragraph(f"<b>{a}</b>", s["body"]), Paragraph(b, s["body"])] for a, b in rows]
    t = Table(data, colWidths=[3.8 * cm, 12.7 * cm])
    t.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (0, -1), SAND),
                ("BOX", (0, 0), (-1, -1), 0.6, LINE),
                ("INNERGRID", (0, 0), (-1, -1), 0.4, LINE),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 8),
                ("RIGHTPADDING", (0, 0), (-1, -1), 8),
                ("TOPPADDING", (0, 0), (-1, -1), 6),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
            ]
        )
    )
    return t


def add_header_footer(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(BRAND)
    canvas.rect(0, A4[1] - 14, A4[0], 14, fill=1, stroke=0)
    canvas.setFillColor(MUTED)
    canvas.setFont("Sarabun", 9)
    canvas.drawString(2 * cm, 1.2 * cm, "SCi-Safety | คู่มือการใช้งานสำหรับผู้ใช้ทุกสิทธิ์")
    canvas.drawRightString(A4[0] - 2 * cm, 1.2 * cm, f"หน้า {doc.page}")
    canvas.restoreState()


def build() -> Path:
    ensure_fonts()
    IMG_DIR.mkdir(parents=True, exist_ok=True)

    flow = draw_flow_overview()
    login = draw_login_steps()
    roles = draw_role_cards()
    requester = draw_requester_steps()
    officer = draw_officer_steps()
    review = draw_review_steps()

    s = styles()
    story = []

    # Cover via table block
    cover = Table(
        [[
            Paragraph("คู่มือการใช้งานระบบ", s["cover_title"]),
        ], [
            Paragraph("SCi-Safety", s["cover_title"]),
        ], [
            Paragraph(
                "ระบบบริหารจัดการคำขอตรวจสอบภาพจากกล้องวงจรปิด<br/>คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น",
                s["cover_sub"],
            ),
        ], [
            Paragraph("ฉบับผู้ใช้งานทุกสิทธิ์ | อธิบายเป็นขั้นตอน พร้อมภาพประกอบ", s["cover_sub"]),
        ]],
        colWidths=[16.5 * cm],
    )
    cover.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), BRAND_DEEP),
                ("TOPPADDING", (0, 0), (-1, 0), 40),
                ("BOTTOMPADDING", (0, -1), (-1, -1), 36),
                ("TOPPADDING", (0, 1), (-1, -2), 8),
                ("BOTTOMPADDING", (0, 1), (-1, -2), 8),
                ("ALIGN", (0, 0), (-1, -1), "CENTER"),
                ("LEFTPADDING", (0, 0), (-1, -1), 18),
                ("RIGHTPADDING", (0, 0), (-1, -1), 18),
            ]
        )
    )
    story += [Spacer(1, 3.5 * cm), cover, Spacer(1, 1.2 * cm)]
    story.append(Paragraph("เอกสารนี้อธิบายวิธีใช้งานจริงของแต่ละสิทธิ์ ไม่ใช้สัญลักษณ์พิเศษที่อาจอ่านไม่ออกในบางเครื่องอ่าน PDF", s["center"]))
    story.append(Paragraph("เวอร์ชันเอกสาร: ตุลาคม 2026", s["center"]))
    story.append(PageBreak())

    # TOC-like intro
    story.append(Paragraph("1. เกี่ยวกับระบบ", s["h1"]))
    story.append(Paragraph(
        "SCi-Safety ใช้รับคำขอให้เจ้าหน้าที่ช่วยตรวจสอบภาพจากกล้องวงจรปิดของคณะวิทยาศาสตร์ "
        "การดูภาพทำได้เฉพาะต่อหน้าเจ้าหน้าที่ในห้องควบคุมเท่านั้น "
        "<b>ไม่มีการดาวน์โหลด ไม่มีการส่งมอบไฟล์ภาพ และไม่มีการบันทึกภาพหน้าจอ</b>",
        s["body"],
    ))
    story.append(Paragraph("สิ่งที่ควรรู้ก่อนเริ่ม", s["h2"]))
    story.extend(bullets([
        "เข้าสู่ระบบด้วยบัญชี Google ที่ได้รับอนุญาต",
        "เมนูที่เห็นขึ้นกับสิทธิ์ที่ผู้ดูแลระบบกำหนด",
        "ทุกการดำเนินการสำคัญถูกบันทึกในบันทึกการใช้งาน (Audit)",
        "หากไม่พบอาคารหรือห้องในรายการ สามารถพิมพ์เองได้",
    ], s["bullet"]))
    story.append(figure(flow, caption="ภาพที่ 1 ภาพรวมเส้นทางคำขอตั้งแต่ยื่นจนปิดเรื่อง", s=s))

    story.append(Paragraph("2. เริ่มต้นใช้งาน (ทุกสิทธิ์)", s["h1"]))
    story.append(figure(login, caption="ภาพที่ 2 ขั้นตอนเข้าสู่ระบบ", s=s))
    story.append(step_table([
        ("ขั้นที่ 1", "เปิดระบบที่ http://localhost:8088 หรือที่อยู่ที่คณะแจ้ง"),
        ("ขั้นที่ 2", "กดเข้าสู่ระบบด้วย Google แล้วเลือกบัญชีของท่าน"),
        ("ขั้นที่ 3", "ระบบพาไปหน้าหลัก เมนูจะตรงกับสิทธิ์ของท่าน"),
        ("ออกจากระบบ", "กดปุ่มออกจากระบบที่มุมขวาบนเมื่อใช้งานเสร็จ"),
    ], s))
    story.append(Spacer(1, 8))
    story.append(figure(roles, width=16.5 * cm, caption="ภาพที่ 3 สิทธิ์ผู้ใช้ทั้ง 6 บทบาท", s=s))
    story.append(PageBreak())

    # Requester
    story.append(Paragraph("3. ผู้ยื่นคำขอ", s["h1"]))
    story.append(Paragraph(
        "เหมาะสำหรับนักศึกษา บุคลากร และบุคคลภายนอกที่ต้องการให้เจ้าหน้าที่ช่วยตรวจภาพ",
        s["body"],
    ))
    story.append(Paragraph("งานหลักที่ทำได้", s["h2"]))
    story.extend(bullets([
        "แจ้งเรื่อง / ยื่นคำขอใหม่",
        "ติดตามคำขอของตนเอง",
        "ดูและยืนยันนัดหมายดูภาพ",
    ], s["bullet"]))
    story.append(figure(requester, width=16.5 * cm, caption="ภาพที่ 4 แบบฟอร์มยื่นคำขอ 3 ขั้น", s=s))
    story.append(Paragraph("ขั้นตอนยื่นคำขอ", s["h2"]))
    story.append(step_table([
        ("1", "จากหน้าหลัก กด “แจ้งเรื่องที่ต้องการให้ช่วยดู” หรือเมนูแจ้งเรื่อง"),
        ("2", "ขั้นแนะนำตัว: ตรวจชื่อ-อีเมล เลือกฐานะ กรอกสังกัดและเบอร์ติดต่อ แล้วกดถัดไป"),
        ("3", "ขั้นเหตุการณ์: เลือกประเภท ช่วงเวลา อาคาร ชั้น และพิมพ์ห้อง/จุดที่จำได้"),
        ("4", "เล่าเหตุการณ์เท่าที่จำได้ ใส่จุดสังเกตถ้ามี แล้วกดถัดไป"),
        ("5", "อ่านข้อตกลง ทำเครื่องหมายรับทราบ แล้วกดส่งคำขอ"),
        ("6", "จำเลขที่คำขอไว้ แล้วติดตามสถานะที่เมนูคำขอของฉัน"),
    ], s))
    story.append(Paragraph("หลังส่งคำขอแล้วทำอะไรต่อ", s["h2"]))
    story.extend(bullets([
        "หากสถานะเป็น “ขอข้อมูลเพิ่ม” ให้เปิดคำขอแล้วส่งข้อมูลเพิ่มตามที่เจ้าหน้าที่ระบุ",
        "หากสถานะเป็น “นัดดูภาพแล้ว” ให้เปิดหน้านัดหมายและกดยืนยันนัด",
        "วันนัด ให้ไปที่ห้องควบคุมตามเวลาที่กำหนด เจ้าหน้าที่จะควบคุมการดูภาพ",
    ], s["bullet"]))
    story.append(PageBreak())

    # CCTV Admin
    story.append(Paragraph("4. เจ้าหน้าที่ผู้รับผิดชอบคำขอ", s["h1"]))
    story.append(Paragraph(
        "เป็นผู้รับเรื่อง ตรวจความเป็นไปได้ทางเทคนิค นัดหมาย และปิดเรื่อง",
        s["body"],
    ))
    story.append(figure(officer, width=16.5 * cm, caption="ภาพที่ 5 ลำดับงานของเจ้าหน้าที่ในคิว", s=s))
    story.append(Paragraph("ขั้นตอนใช้งานประจำวัน", s["h2"]))
    story.append(step_table([
        ("1", "เข้าเมนู “คิวงาน” เพื่อดูคำขอที่รอดำเนินการ"),
        ("2", "เปิดคำขอ อ่านรายละเอียดสถานที่และช่วงเวลา"),
        ("3", "บันทึกผลการตรวจทางเทคนิค เช่น พบภาพ / ไม่พบภาพ / ไม่มีกล้องครอบคลุม"),
        ("4", "หากต้องขอข้อมูลเพิ่ม ให้ส่งกลับผู้ยื่นพร้อมข้อความที่ชัดเจน"),
        ("5", "เมื่อผ่านเงื่อนไข ระบบจะส่งต่อไปยังผู้พิจารณาตามกฎเส้นทาง หรืออนุญาตนัดดูได้เอง"),
        ("6", "เมื่ออนุญาตแล้ว ให้กำหนดวัน เวลา สถานที่ และผู้เข้าห้องควบคุม"),
        ("7", "หลังการดูภาพ บันทึกผลและปิดเรื่อง"),
    ], s))
    story.append(Paragraph("งานอื่นที่เกี่ยวข้อง", s["h2"]))
    story.extend(bullets([
        "ทะเบียนกล้อง: เพิ่ม/ตรวจสถานะกล้อง จุดเสี่ยง และงานซ่อม",
        "บันทึกคำขอแทนผู้แจ้งได้เมื่อรับเรื่องทางช่องทางอื่น (ถ้ามีสิทธิ์)",
        "ติดตามนัดหมายของผู้ยื่นผ่านเมนูนัดหมาย",
    ], s["bullet"]))
    story.append(PageBreak())

    # Director / Dean
    story.append(Paragraph("5. ผู้อำนวยการกองบริหารงานคณะ", s["h1"]))
    story.append(Paragraph(
        "พิจารณาคำขอที่กฎเส้นทางกำหนดให้ผ่านผู้อำนวยการ และดูรายงานภาพรวม",
        s["body"],
    ))
    story.append(figure(review, width=16.5 * cm, caption="ภาพที่ 6 ตัวเลือกการพิจารณา", s=s))
    story.append(step_table([
        ("1", "เข้าเมนู “พิจารณา” เพื่อดูกล่องงานที่รอท่าน"),
        ("2", "เปิดคำขอ อ่านเหตุผล ผลตรวจทางเทคนิค และความเสี่ยง"),
        ("3", "เลือก เห็นชอบ / ขอข้อมูลเพิ่ม / ไม่เห็นชอบ พร้อมความเห็น"),
        ("4", "กรณีสำคัญบางเรื่องอาจส่งต่อไปยังรองคณบดีตามกฎที่ตั้งไว้"),
        ("5", "ดูรายงานหรือส่งออกข้อมูลแบบปกปิดข้อมูลส่วนบุคคลได้จากเมนูรายงาน"),
    ], s))

    story.append(Paragraph("6. รองคณบดีฝ่ายบริหาร", s["h1"]))
    story.append(Paragraph(
        "พิจารณาคำขอระดับสูงหรือประเภทที่กฎเส้นทางกำหนดให้ผ่านรองคณบดี",
        s["body"],
    ))
    story.append(step_table([
        ("1", "เข้าเมนู “พิจารณา” เช่นเดียวกับผู้อำนวยการ"),
        ("2", "ตรวจคำขอที่สถานะรอรองคณบดีฝ่ายบริหาร"),
        ("3", "บันทึกผลการพิจารณาและเหตุผล"),
        ("4", "ใช้เมนูรายงานเพื่อติดตามสถิติและจุดบกพร่องประกอบการบริหาร"),
    ], s))
    story.append(PageBreak())

    # PDPA
    story.append(Paragraph("7. เจ้าหน้าที่ประสานงานคุ้มครองข้อมูลส่วนบุคคล (PDPA)", s["h1"]))
    story.append(Paragraph(
        "ให้ความเห็นเมื่อคำขอมีความเสี่ยงด้านข้อมูลส่วนบุคคลสูง และดูแลประกาศความเป็นส่วนตัว",
        s["body"],
    ))
    story.append(step_table([
        ("1", "เข้าเมนูกล่องงานด้านข้อมูลส่วนบุคคล"),
        ("2", "เปิดคำขอที่รอความเห็น ตรวจวัตถุประสงค์และขอบเขตการดูภาพ"),
        ("3", "บันทึกความเห็น/เงื่อนไขด้าน PDPA"),
        ("4", "เมื่อจำเป็น ปรับปรุงประกาศความเป็นส่วนตัวที่เมนูประกาศ แล้วเผยแพร่รุ่นใหม่"),
    ], s))
    story.append(Paragraph("ข้อควรระวัง", s["h2"]))
    story.extend(bullets([
        "เน้นหลักความจำเป็นและสัดส่วน ไม่ขยายขอบเขตเกินเหตุ",
        "ทุกความเห็นถูกบันทึกเพื่อตรวจสอบย้อนหลังได้",
    ], s["bullet"]))

    # Super Admin
    story.append(Paragraph("8. ผู้ดูแลระบบสูงสุด", s["h1"]))
    story.append(Paragraph(
        "กำหนดสิทธิ์ผู้ใช้ ปรับเส้นทางคำขอ ตรวจบันทึก และดูแลภาพรวมระบบ",
        s["body"],
    ))
    story.append(step_table([
        ("1", "เมนูสิทธิ์: กำหนดบทบาทให้ผู้ใช้ที่เคยเข้าสู่ระบบแล้ว"),
        ("2", "กำหนดเจ้าหน้าที่สำรองตามช่วงวันที่ หากเจ้าหน้าที่หลักไม่อยู่"),
        ("3", "เมนูเส้นทางคำขอ: เลือกว่าประเภทเหตุใดต้องผ่านเจ้าหน้าที่ / ผู้อำนวยการ / รองคณบดี"),
        ("4", "เมนูบันทึกการใช้งาน: อ่านย้อนหลังแบบอ่านอย่างเดียว"),
        ("5", "เมนูรายงาน/ทะเบียนกล้อง/ประกาศ: ดูแลองค์ประกอบระบบที่เกี่ยวข้อง"),
    ], s))
    story.append(Paragraph("โหมดทดสอบสิทธิ์ (ใช้ชั่วคราวช่วงทดสอบระบบ)", s["h2"]))
    story.extend(bullets([
        "เปิดได้เมื่อตั้งค่า IMPERSONATION_ENABLED=true",
        "ที่หน้าสิทธิ์ เลือก “เข้าใช้งานด้วยสิทธิ์นี้” เพื่อทดสอบเมนูของแต่ละบทบาท",
        "มีแถบแจ้งเตือนด้านบน และปุ่มกลับสู่บัญชีผู้ดูแล",
        "เมื่อทดสอบเสร็จ ให้ปิดค่านี้เป็น false ทันที",
    ], s["bullet"]))
    story.append(PageBreak())

    # Status dictionary
    story.append(Paragraph("9. ความหมายสถานะคำขอ", s["h1"]))
    status_rows = [
        ("รอเจ้าหน้าที่ตรวจสอบ", "เพิ่งยื่น คิวอยู่ที่เจ้าหน้าที่"),
        ("ขอข้อมูลเพิ่ม", "ต้องเติมรายละเอียดแล้วส่งกลับ"),
        ("รอผู้อำนวยการพิจารณา", "อยู่ระหว่างพิจารณาของผู้อำนวยการ"),
        ("รอรองคณบดีฝ่ายบริหาร", "อยู่ระหว่างพิจารณาของรองคณบดี"),
        ("รอความเห็นด้านข้อมูลส่วนบุคคล", "อยู่ระหว่างความเห็น PDPA"),
        ("อนุญาตให้นัดดูภาพ", "พร้อมให้นัดหมาย"),
        ("นัดดูภาพแล้ว", "มีวันเวลาแล้ว ควรกดยืนยันนัด"),
        ("ดูภาพแล้ว", "ผ่านการดูภาพแล้ว รอปิดเรื่อง"),
        ("ปิดเรื่อง", "สิ้นสุดกระบวนการ"),
        ("ไม่เห็นชอบ", "คำขอไม่ผ่านการพิจารณา"),
        ("ไม่พบภาพหรือไม่มีกล้อง", "ตรวจแล้วไม่มีภาพใช้ได้"),
    ]
    story.append(step_table(status_rows, s))

    story.append(Paragraph("10. คำถามที่พบบ่อย", s["h1"]))
    story.append(Paragraph("<b>ทำไมฉันไม่เห็นเมนูคิวงานหรือพิจารณา?</b>", s["h3"]))
    story.append(Paragraph("เมนูขึ้นกับสิทธิ์ ติดต่อผู้ดูแลระบบสูงสุดเพื่อกำหนดบทบาทให้ถูกต้อง", s["body"]))
    story.append(Paragraph("<b>หาห้องในรายการไม่เจอทำอย่างไร?</b>", s["h3"]))
    story.append(Paragraph("พิมพ์ชื่อห้องหรือจุดที่จำได้เองในช่อง “ห้องหรือจุดที่จำได้” ได้เลย", s["body"]))
    story.append(Paragraph("<b>จะได้ไฟล์วีดีโอไหม?</b>", s["h3"]))
    story.append(Paragraph("ไม่ได้ ระบบรองรับเฉพาะการนัดดูภาพต่อหน้าเจ้าหน้าที่ในห้องควบคุม", s["body"]))
    story.append(Paragraph("<b>เข้าสู่ระบบไม่ได้</b>", s["h3"]))
    story.append(Paragraph("ตรวจว่าใช้บัญชีที่ได้รับอนุญาต และอนุญาตป๊อปอัป/คุกกี้ของเบราว์เซอร์สำหรับ Google Login", s["body"]))

    story.append(Spacer(1, 16))
    story.append(Paragraph("ช่องทางติดต่อสนับสนุน", s["h2"]))
    story.append(Paragraph(
        "หากพบปัญหาการใช้งานหรือต้องการปรับสิทธิ์ ติดต่อผู้ดูแลระบบของคณะวิทยาศาสตร์ "
        "พร้อมแจ้งอีเมลที่เข้าสู่ระบบและเลขที่คำขอ (ถ้ามี)",
        s["body"],
    ))

    doc = SimpleDocTemplate(
        str(OUT_PDF),
        pagesize=A4,
        leftMargin=2 * cm,
        rightMargin=2 * cm,
        topMargin=1.8 * cm,
        bottomMargin=1.8 * cm,
        title="คู่มือการใช้งาน SCi-Safety",
        author="SCi-Safety",
    )
    doc.build(story, onFirstPage=add_header_footer, onLaterPages=add_header_footer)
    return OUT_PDF


if __name__ == "__main__":
    path = build()
    print(path)
    print("bytes", path.stat().st_size)
