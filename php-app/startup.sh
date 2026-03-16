#!/bin/bash
# ============================================================
# startup.sh — Azure App Service Linux (PHP 8.x)
#
# Este script é executado na inicialização do container.
# Configura o Nginx para aceitar uploads grandes e recarrega.
#
# COMO CONFIGURAR:
#   Azure Portal > App Service > Configuration > General settings
#   > Startup Command: /home/site/wwwroot/startup.sh
# ============================================================

echo "[startup.sh] Configurando limites de upload..."

# --- 1) Corrigir o Nginx: client_max_body_size ---
NGINX_CONF="/etc/nginx/sites-available/default"

if [ -f "$NGINX_CONF" ]; then
    # Se já tiver client_max_body_size, substitui
    if grep -q "client_max_body_size" "$NGINX_CONF"; then
        sed -i 's/client_max_body_size[[:space:]]*[0-9]*[mMkKgG]*/client_max_body_size 200M/g' "$NGINX_CONF"
    else
        # Se não tiver, adiciona dentro do bloco server
        sed -i '/server[[:space:]]*{/a \    client_max_body_size 200M;' "$NGINX_CONF"
    fi
    echo "[startup.sh] Nginx: client_max_body_size = 200M"
else
    echo "[startup.sh] AVISO: $NGINX_CONF nao encontrado, tentando /etc/nginx/nginx.conf"
    NGINX_CONF="/etc/nginx/nginx.conf"
    if [ -f "$NGINX_CONF" ]; then
        if grep -q "client_max_body_size" "$NGINX_CONF"; then
            sed -i 's/client_max_body_size[[:space:]]*[0-9]*[mMkKgG]*/client_max_body_size 200M/g' "$NGINX_CONF"
        else
            sed -i '/http[[:space:]]*{/a \    client_max_body_size 200M;' "$NGINX_CONF"
        fi
        echo "[startup.sh] Nginx (nginx.conf): client_max_body_size = 200M"
    fi
fi

# --- 2) Corrigir PHP-FPM: upload limits ---
# O .user.ini na raiz do document root é lido automaticamente,
# mas como fallback forçamos via PHP-FPM config
PHP_FPM_INI="/usr/local/etc/php/conf.d/uploads.ini"
cat > "$PHP_FPM_INI" << 'EOF'
upload_max_filesize = 200M
post_max_size = 210M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
EOF
echo "[startup.sh] PHP: upload_max_filesize = 200M, post_max_size = 210M"

# --- 3) Reiniciar serviços ---
# Tenta recarregar Nginx (depende da imagem do App Service)
if command -v nginx &> /dev/null; then
    nginx -t 2>/dev/null && nginx -s reload 2>/dev/null
    echo "[startup.sh] Nginx recarregado com sucesso"
fi

# Tenta reiniciar PHP-FPM
if command -v php-fpm &> /dev/null; then
    kill -USR2 $(cat /var/run/php-fpm.pid 2>/dev/null) 2>/dev/null || true
    echo "[startup.sh] PHP-FPM sinalizado para reload"
fi

echo "[startup.sh] Configuracao concluida!"
