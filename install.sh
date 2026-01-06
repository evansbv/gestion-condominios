#!/bin/bash

# Script de instalación rápida
# Sistema de Gestión de Condominios

echo "🏢 Instalando Sistema de Gestión de Condominios..."
echo ""

# Colores para mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar si composer está instalado
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer no está instalado. Por favor instálelo primero.${NC}"
    exit 1
fi

# Verificar si npm está instalado
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ NPM no está instalado. Por favor instálelo primero.${NC}"
    exit 1
fi

# Verificar si psql está instalado
if ! command -v psql &> /dev/null; then
    echo -e "${YELLOW}⚠️  PostgreSQL no está instalado o no está en el PATH.${NC}"
fi

# Paso 1: Instalar dependencias de Composer
echo -e "${YELLOW}📦 Instalando dependencias de PHP (Composer)...${NC}"
composer install --no-interaction

# Paso 2: Generar clave de aplicación
echo -e "${YELLOW}🔑 Generando clave de aplicación...${NC}"
php artisan key:generate --no-interaction

# Paso 3: Crear enlace de storage
echo -e "${YELLOW}🔗 Creando enlace simbólico de storage...${NC}"
php artisan storage:link

# Paso 4: Instalar dependencias de NPM
echo -e "${YELLOW}📦 Instalando dependencias de Node.js (NPM)...${NC}"
npm install

# Paso 5: Ejecutar migraciones (preguntar al usuario)
echo ""
read -p "¿Desea ejecutar las migraciones de base de datos? (s/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${YELLOW}🗄️  Ejecutando migraciones...${NC}"
    php artisan migrate --no-interaction

    # Preguntar si desea ejecutar seeders
    read -p "¿Desea cargar datos de prueba (seeders)? (s/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        echo -e "${YELLOW}🌱 Cargando datos de prueba...${NC}"
        php artisan db:seed --no-interaction

        echo ""
        echo -e "${GREEN}✅ Datos de prueba cargados exitosamente!${NC}"
        echo -e "${GREEN}   Usuario Admin: admin@tecnoweb.org.bo / admin123${NC}"
        echo -e "${GREEN}   Usuario Directorio: directorio@tecnoweb.org.bo / directorio123${NC}"
        echo -e "${GREEN}   Usuario Propietario: maria@example.com / propietario123${NC}"
    fi
else
    echo -e "${YELLOW}⏭️  Migraciones omitidas${NC}"
fi

# Paso 6: Compilar assets
echo ""
read -p "¿Desea compilar los assets para desarrollo? (s/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${YELLOW}🎨 Compilando assets para desarrollo...${NC}"
    npm run build
else
    echo -e "${YELLOW}⏭️  Compilación de assets omitida${NC}"
fi

# Resumen
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ ¡Instalación completada!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}📚 Pasos siguientes:${NC}"
echo ""
echo "1. Verificar configuración de base de datos en .env"
echo "2. Ejecutar: php artisan serve"
echo "3. Abrir en navegador: http://localhost:8000"
echo ""
echo -e "${YELLOW}📖 Documentación:${NC}"
echo "   - Ver INSTALACION.md para guía detallada"
echo "   - Ver PROYECTO.md para documentación del sistema"
echo ""
echo -e "${GREEN}¡Gracias por usar el Sistema de Gestión de Condominios!${NC}"
