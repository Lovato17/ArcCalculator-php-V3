#!/bin/bash
# ============================================================
# startup.sh — Azure App Service Linux (PHP 8.2)
# Container: Nginx + PHP-FPM
#
# CONFIGURAÇÃO:
#   Azure Portal > App Service > Configuration > General settings
#   > Startup Command: /home/site/wwwroot/startup.sh
# ============================================================

echo "=========================================="
echo "[startup.sh] Iniciando configuracao..."
echo "=========================================="

# --- 1) Criar Nginx server block customizado ---
# O container PHP do Azure lê /home/site/default automaticamente
cat > /home/site/default << 'NGINX_EOF'
server {
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot/public;
    index home.php index.php index.html;
    server_name _;
    port_in_redirect off;

    # ============ LIMITE DE UPLOAD (200 MB) ============
    client_max_body_size 200M;
    client_body_timeout 300s;
    proxy_read_timeout 300s;
    fastcgi_read_timeout 300s;

    # Servir arquivos estáticos diretamente
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$ {
        expires 7d;
        access_log off;
        try_files $uri =404;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_connect_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_read_timeout 300s;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Fallback: tenta arquivo -> diretório -> home.php
    location / {
        try_files $uri $uri/ /home.php?$query_string;
    }
}
NGINX_EOF
echo "[startup.sh] Nginx config criado em /home/site/default"
echo "[startup.sh]   - client_max_body_size = 200M"
echo "[startup.sh]   - document root = /home/site/wwwroot/public"

# --- 2) Configurar limites PHP ---
PHP_INI_DIR="/usr/local/etc/php/conf.d"
if [ -d "$PHP_INI_DIR" ]; then
    cat > "$PHP_INI_DIR/99-custom-uploads.ini" << 'PHP_EOF'
upload_max_filesize = 200M
post_max_size = 210M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_file_uploads = 20
PHP_EOF
    echo "[startup.sh] PHP ini criado: upload_max_filesize=200M, post_max_size=210M"
else
    echo "[startup.sh] AVISO: $PHP_INI_DIR nao encontrado, tentando /home/site/ini..."
    mkdir -p /home/site/ini
    cat > /home/site/ini/uploads.ini << 'PHP_EOF'
upload_max_filesize = 200M
post_max_size = 210M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
PHP_EOF
    echo "[startup.sh] PHP ini criado em /home/site/ini/uploads.ini (fallback)"
fi

# --- 3) Garantir permissões do diretório de uploads ---
mkdir -p /home/site/wwwroot/uploads
chmod 755 /home/site/wwwroot/uploads
echo "[startup.sh] Diretorio uploads/ criado com permissoes corretas"

echo "=========================================="
echo "[startup.sh] Configuracao concluida!"
echo "[startup.sh] Verificacao:"
php -r "echo '  upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo '  post_max_size:       ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo '  memory_limit:        ' . ini_get('memory_limit') . PHP_EOL;"
echo "=========================================="
