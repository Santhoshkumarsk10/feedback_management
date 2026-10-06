import sys
import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.pdfgen import canvas
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle

PDF_PATH = "/var/www/html/Office/freelance/feedback/feedback_management/DEPLOYMENT_GUIDE.pdf"

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_header_footer(num_pages)
            super().showPage()
        super().save()

    def draw_header_footer(self, page_count):
        self.saveState()
        # Header on pages 2+
        if self._pageNumber > 1:
            self.setFont('Helvetica-Bold', 7.5)
            self.setFillColor(colors.HexColor('#1e3a8a'))
            self.drawString(40, 810, 'SHIBAURA MACHINE INDIA')
            self.setFont('Helvetica', 7.5)
            self.setFillColor(colors.HexColor('#64748b'))
            self.drawString(165, 810, '|  shibaura.amoebatronix.com — Production Deployment Manual')
            self.setStrokeColor(colors.HexColor('#cbd5e1'))
            self.setLineWidth(0.5)
            self.line(40, 803, 555, 803)

        # Footer on all pages
        self.setStrokeColor(colors.HexColor('#cbd5e1'))
        self.setLineWidth(0.5)
        self.line(40, 42, 555, 42)
        self.setFont('Helvetica', 7.5)
        self.setFillColor(colors.HexColor('#64748b'))
        self.drawString(40, 30, 'Target Host: /home/john/shibaura/feedback_management | shibaura.amoebatronix.com')
        page_str = f"Page {self._pageNumber} of {page_count}"
        self.drawRightString(555, 30, page_str)
        self.restoreState()

def create_pdf():
    doc = SimpleDocTemplate(
        PDF_PATH,
        pagesize=A4,
        leftMargin=40,
        rightMargin=40,
        topMargin=46,
        bottomMargin=46
    )

    styles = getSampleStyleSheet()

    # Typography styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=19,
        leading=23,
        textColor=colors.HexColor('#0f172a')
    )
    subtitle_style = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=14,
        textColor=colors.HexColor('#2563eb')
    )
    meta_style = ParagraphStyle(
        'DocMeta',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor('#64748b')
    )
    h1_style = ParagraphStyle(
        'SectionH1',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10.5,
        leading=14,
        textColor=colors.HexColor('#1e3a8a'),
        spaceBefore=9,
        spaceAfter=3,
        keepWithNext=True
    )
    h2_style = ParagraphStyle(
        'SectionH2',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=12.5,
        textColor=colors.HexColor('#0f172a'),
        spaceBefore=5,
        spaceAfter=2,
        keepWithNext=True
    )
    body_style = ParagraphStyle(
        'BodyDark',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11.5,
        textColor=colors.HexColor('#334155'),
        spaceAfter=3
    )
    bullet_style = ParagraphStyle(
        'BulletText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.8,
        leading=11,
        textColor=colors.HexColor('#334155'),
        leftIndent=12,
        firstLineIndent=-8,
        spaceAfter=2
    )
    code_style = ParagraphStyle(
        'CodeStyle',
        parent=styles['Normal'],
        fontName='Courier',
        fontSize=7.1,
        leading=9.5,
        textColor=colors.HexColor('#0f172a')
    )
    info_box_style = ParagraphStyle(
        'InfoBoxStyle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.8,
        leading=11,
        textColor=colors.HexColor('#1e40af')
    )
    warn_box_style = ParagraphStyle(
        'WarnBoxStyle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.8,
        leading=11,
        textColor=colors.HexColor('#92400e')
    )

    def code_box(code_text):
        escaped = code_text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;').replace('\n', '<br/>')
        p = Paragraph(escaped, code_style)
        t = Table([[p]], colWidths=[515])
        t.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
            ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#cbd5e1')),
            ('LINELEFT', (0,0), (-1,-1), 2.5, colors.HexColor('#2563eb')),
            ('TOPPADDING', (0,0), (-1,-1), 3.5),
            ('BOTTOMPADDING', (0,0), (-1,-1), 3.5),
            ('LEFTPADDING', (0,0), (-1,-1), 8),
            ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ]))
        return t

    def info_box(text):
        p = Paragraph(f"<b>NOTE:</b> {text}", info_box_style)
        t = Table([[p]], colWidths=[515])
        t.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#eff6ff')),
            ('BOX', (0,0), (-1,-1), 0.8, colors.HexColor('#93c5fd')),
            ('TOPPADDING', (0,0), (-1,-1), 3.5),
            ('BOTTOMPADDING', (0,0), (-1,-1), 3.5),
            ('LEFTPADDING', (0,0), (-1,-1), 8),
            ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ]))
        return t

    def warn_box(text):
        p = Paragraph(f"<b>CRITICAL:</b> {text}", warn_box_style)
        t = Table([[p]], colWidths=[515])
        t.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#fffbeb')),
            ('BOX', (0,0), (-1,-1), 0.8, colors.HexColor('#fcd34d')),
            ('TOPPADDING', (0,0), (-1,-1), 3.5),
            ('BOTTOMPADDING', (0,0), (-1,-1), 3.5),
            ('LEFTPADDING', (0,0), (-1,-1), 8),
            ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ]))
        return t

    story = []

    # Title block
    story.append(Paragraph("Shibaura Plant Feedback Management System", title_style))
    story.append(Spacer(1, 2))
    story.append(Paragraph("PRODUCTION SERVER DEPLOYMENT MANUAL — shibaura.amoebatronix.com", subtitle_style))
    story.append(Spacer(1, 2))
    story.append(Paragraph("Domain: <b>http://shibaura.amoebatronix.com</b> | Path: <b>/home/john/shibaura/feedback_management</b> | User: <b>john</b>", meta_style))
    story.append(Spacer(1, 4))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#1e3a8a'), spaceAfter=7))

    # Architecture Overview Table
    th_style = ParagraphStyle('TH', parent=body_style, fontName='Helvetica-Bold', fontSize=7.8, textColor=colors.white)
    td_style = ParagraphStyle('TD', parent=body_style, fontSize=7.5, leading=10)
    summary_data = [
        [Paragraph("Parameter", th_style), Paragraph("Production Specification", th_style), Paragraph("Role / Scope", th_style)],
        [Paragraph("Target Domain", td_style), Paragraph("http://shibaura.amoebatronix.com (SSL Ready)", td_style), Paragraph("Public Gateway", td_style)],
        [Paragraph("Server Path", td_style), Paragraph("/home/john/shibaura/feedback_management", td_style), Paragraph("Application Root Directory", td_style)],
        [Paragraph("Web Document Root", td_style), Paragraph("/home/john/shibaura/feedback_management/public", td_style), Paragraph("Nginx Web Root Directory", td_style)],
        [Paragraph("System User", td_style), Paragraph("john (Member of www-data group)", td_style), Paragraph("Process & File Ownership", td_style)],
        [Paragraph("Backend Stack", td_style), Paragraph("Laravel 12.x / PHP 8.2+ FPM / MySQL 8.0", td_style), Paragraph("Framework & Database Engine", td_style)],
        [Paragraph("Frontend & Auth", td_style), Paragraph("Vite 6 (Node 20) & JWT Auth (HS256)", td_style), Paragraph("UI Bundles & Mobile App Tokens", td_style)],
    ]
    summary_table = Table(summary_data, colWidths=[115, 235, 165])
    summary_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#1e3a8a')),
        ('TEXTCOLOR', (0,0), (-1,0), colors.white),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2),
        ('TOPPADDING', (0,0), (-1,-1), 2),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.HexColor('#f8fafc'), colors.white]),
    ]))
    story.append(summary_table)
    story.append(Spacer(1, 6))

    # Step 1
    s1 = []
    s1.append(Paragraph("1. Server Preparation & Firewall Configuration", h1_style))
    s1.append(Paragraph("Connect to your server via SSH as user <b>john</b> and prepare system packages:", body_style))
    s1.append(code_box("""ssh john@your_server_ip
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl wget zip unzip ufw software-properties-common ca-certificates
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable"""))
    story.append(KeepTogether(s1))

    # Step 2
    s2 = []
    s2.append(Paragraph("2. Installing PHP 8.2 & Core Extensions", h1_style))
    s2.append(Paragraph("Install PHP 8.2, required PHP extensions for Laravel 12 & JWT, and Composer 2:", body_style))
    s2.append(code_box("""sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \\
                    php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip \\
                    php8.2-gd php8.2-intl php8.2-bcmath
# Install Composer 2 globally:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer"""))
    story.append(KeepTogether(s2))

    # Step 3
    s3 = []
    s3.append(Paragraph("3. MySQL 8.0 Database Setup & Dedicated User (devteam)", h1_style))
    s3.append(Paragraph("Create the database and grant full privileges to user <b>devteam</b>:", body_style))
    s3.append(code_box("""sudo apt install -y mysql-server && sudo mysql_secure_installation
# Run in MySQL shell:
sudo mysql -u root -p
CREATE DATABASE IF NOT EXISTS plant_feedback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Grant privileges to existing user devteam:
GRANT ALL PRIVILEGES ON plant_feedback.* TO 'devteam'@'localhost';

# Or create devteam user if not exists:
# CREATE USER IF NOT EXISTS 'devteam'@'localhost' IDENTIFIED BY 'your_password';
# GRANT ALL PRIVILEGES ON plant_feedback.* TO 'devteam'@'localhost';

FLUSH PRIVILEGES;
EXIT;"""))
    story.append(KeepTogether(s3))

    # Step 4
    s4 = []
    s4.append(Paragraph("4. Installing Node.js 20 LTS (Vite Compilation)", h1_style))
    s4.append(code_box("""curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs"""))
    story.append(KeepTogether(s4))

    # Step 5
    s5 = []
    s5.append(Paragraph("5. Codebase Setup in /home/john/shibaura/feedback_management", h1_style))
    s5.append(Paragraph("Clone the application repository from GitHub into the user path and install PHP packages:", body_style))
    s5.append(code_box("""mkdir -p /home/john/shibaura
cd /home/john/shibaura
git clone https://github.com/Santhoshkumarsk10/feedback_management.git feedback_management
cd /home/john/shibaura/feedback_management
git checkout develop_santhosh # or main
composer install --no-dev --optimize-autoloader --no-interaction"""))
    story.append(KeepTogether(s5))

    # Step 6
    s6 = []
    s6.append(Paragraph("6. Production Environment (.env) & Cryptographic Keys", h1_style))
    s6.append(Paragraph("Configure production variables in <b>.env</b> and generate the application and JWT secrets:", body_style))
    s6.append(code_box("""cp .env.example .env
nano .env

# Essential .env values for shibaura.amoebatronix.com:
APP_NAME="Shibaura Plant Feedback"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://shibaura.amoebatronix.com
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plant_feedback
DB_USERNAME=devteam
DB_PASSWORD=your_devteam_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database"""))
    s6.append(Spacer(1, 2))
    s6.append(code_box("""php artisan key:generate
php artisan jwt:secret
php artisan storage:link"""))
    s6.append(Spacer(1, 2))
    s6.append(warn_box("Running <b>php artisan jwt:secret</b> is mandatory! The mobile application and visitor authentication API will fail with a 500 error if JWT_SECRET is empty."))
    story.append(KeepTogether(s6))

    # Step 7
    s7 = []
    s7.append(Paragraph("7. Database Migrations & Initial Master Seeders", h1_style))
    s7.append(Paragraph("Execute database migrations followed by the project seeders to populate plants, questionnaires, and users:", body_style))
    s7.append(code_box("""php artisan migrate --force
php artisan db:seed --class=PlantSeeder --force
php artisan db:seed --class=MasterSeeder --force
php artisan db:seed --class=CompanyAndBannerSeeder --force"""))
    s7.append(Spacer(1, 2))
    s7.append(info_box("Default accounts seeded: Super Admin (<b>superadmin@plant.test</b>), Admin (<b>admin@plant.test</b>), Organizers (<b>ravi@plant.test</b>). Default password: <b>password</b>. Change immediately upon login."))
    story.append(KeepTogether(s7))

    # Step 8
    s8 = []
    s8.append(Paragraph("8. Compiling Production Frontend Assets (Vite)", h1_style))
    s8.append(Paragraph("Compile production CSS/JS bundles into <b>public/build</b>:", body_style))
    s8.append(code_box("""npm install
npm run build"""))
    story.append(KeepTogether(s8))

    # Step 9
    s9 = []
    s9.append(Paragraph("9. Nginx Web Server Configuration for shibaura.amoebatronix.com", h1_style))
    s9.append(Paragraph("Configure Nginx with root <b>/home/john/shibaura/feedback_management/public</b>:", body_style))
    s9.append(code_box("""sudo apt install -y nginx
sudo cp /home/john/shibaura/feedback_management/deployment/nginx-feedback.conf \\
        /etc/nginx/sites-available/shibaura.amoebatronix.com

sudo ln -s /etc/nginx/sites-available/shibaura.amoebatronix.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx"""))
    story.append(KeepTogether(s9))

    # Step 10
    s10 = []
    s10.append(Paragraph("10. Home Directory Permissions Fix (Resolving 403 Forbidden)", h1_style))
    s10.append(Paragraph("When serving from <b>/home/john/</b>, Nginx (<b>www-data</b>) requires directory traversal permissions:", body_style))
    s10.append(code_box("""# Allow Nginx (www-data) to traverse into /home/john:
chmod +x /home/john
chmod +x /home/john/shibaura

# Add www-data user to group john:
sudo usermod -a -G john www-data

# Set ownership and permissions on writable Laravel directories:
sudo chown -R john:www-data /home/john/shibaura/feedback_management/storage
sudo chown -R john:www-data /home/john/shibaura/feedback_management/bootstrap/cache
sudo chmod -R 775 /home/john/shibaura/feedback_management/storage
sudo chmod -R 775 /home/john/shibaura/feedback_management/bootstrap/cache
chmod 600 /home/john/shibaura/feedback_management/.env"""))
    s10.append(Spacer(1, 2))
    s10.append(warn_box("Skipping <b>chmod +x /home/john</b> will cause Nginx to throw a <b>403 Forbidden</b> error because www-data cannot read the public folder."))
    story.append(KeepTogether(s10))

    # Step 11
    s11 = []
    s11.append(Paragraph("11. Production Caching & Performance Optimization", h1_style))
    s11.append(Paragraph("Warm up Laravel configuration, routing, and blade view caches for peak performance:", body_style))
    s11.append(code_box("""cd /home/john/shibaura/feedback_management
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache"""))
    story.append(KeepTogether(s11))

    # Step 12
    s12 = []
    s12.append(Paragraph("12. SSL Certificate Installation (Let's Encrypt HTTPS)", h1_style))
    s12.append(Paragraph("Secure domain with free Let's Encrypt SSL certificate:", body_style))
    s12.append(code_box("""sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d shibaura.amoebatronix.com"""))
    story.append(KeepTogether(s12))

    # Step 13
    s13 = []
    s13.append(Paragraph("13. Background Queues (Supervisor) & Cron Scheduler", h1_style))
    s13.append(Paragraph("<b>A. Crontab for Laravel Scheduler:</b>", h2_style))
    s13.append(code_box("""crontab -e
# Add line:
* * * * * cd /home/john/shibaura/feedback_management && php artisan schedule:run >> /dev/null 2>&1"""))
    s13.append(Spacer(1, 2))
    s13.append(Paragraph("<b>B. Supervisor for Queue Worker (Database Queue):</b>", h2_style))
    s13.append(code_box("""sudo apt install -y supervisor
sudo cp /home/john/shibaura/feedback_management/deployment/supervisor-queue.conf \\
        /etc/supervisor/conf.d/shibaura-worker.conf
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start shibaura-feedback-worker:*"""))
    story.append(KeepTogether(s13))

    # Step 14
    s14 = []
    s14.append(Paragraph("14. Automated Future Deployments (1-Command Update Script)", h1_style))
    s14.append(Paragraph("Whenever updates are pushed to GitHub, update the server with a single command:", body_style))
    s14.append(code_box("""bash /home/john/shibaura/feedback_management/deployment/deploy.sh"""))
    story.append(KeepTogether(s14))

    # Step 15
    s15 = []
    s15.append(Paragraph("15. Post-Deployment Verification Checklist", h1_style))
    checklist_items = [
        "Web Admin Portal accessible at <b>http://shibaura.amoebatronix.com/login</b>",
        "Super Admin and Plant Admin accounts log in successfully",
        "Mobile App APK download link streams file at <b>/apk/shibaura-plant-feedback.apk</b>",
        "Visitor Feedback Form renders all sections, MCQs, and rating matrices",
        "Storage symlink verified (company logo and banner images render properly)",
        "Log verification: <b>storage/logs/laravel.log</b> shows zero fatal exceptions",
    ]
    for item in checklist_items:
        s15.append(Paragraph(f"[ &#10003; ]  {item}", bullet_style))
    story.append(KeepTogether(s15))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF successfully generated at: {PDF_PATH}")

if __name__ == "__main__":
    create_pdf()
