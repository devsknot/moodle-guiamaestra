# Web Service REST para local_wb_news

Este documento explica cómo activar y consumir el servicio REST de noticias.

## Archivos agregados

- `classes/external/get_news.php` - Implementación del servicio REST
- `db/services.php` - Registro del servicio en Moodle

## Activación en el servidor

### 1. Subir archivos al servidor

Copiar estos archivos al directorio del plugin en el servidor:
```bash
/var/www/html/public/local/wb_news/
```

### 2. Ejecutar upgrade

Ejecutar desde el contenedor o servidor:
```bash
php /var/www/html/public/admin/cli/upgrade.php --non-interactive
```

O si estás usando Docker Compose:
```bash
docker compose exec moodle-guiamaestra php /var/www/html/public/admin/cli/upgrade.php --non-interactive
```

Esto registrará el nuevo servicio `local_wb_news_get_news` en Moodle.

### 3. Habilitar Web Services (si no está habilitado)

En Moodle como administrador:

1. **Administración del sitio** → **Funciones avanzadas**
2. Activar **"Habilitar servicios web"**

### 4. Crear token de acceso

1. **Administración del sitio** → **Servidor** → **Servicios web** → **Gestionar tokens**
2. Clic en **"Añadir"**
3. Seleccionar:
   - **Usuario**: El usuario que consumirá el servicio (puede ser admin o un usuario específico)
   - **Servicio**: `wb_news_service`
4. Guardar y copiar el **token** generado

### 5. Verificar el servicio está registrado

1. **Administración del sitio** → **Servidor** → **Servicios web** → **Vista general**
2. Verificar que aparezca `wb_news_service` en la lista
3. Verificar que la función `local_wb_news_get_news` esté disponible

## Consumo del servicio

### Endpoint

```
https://tu-dominio.com/webservice/rest/server.php
```

### Parámetros

| Parámetro | Tipo | Descripción | Requerido |
|-----------|------|-------------|-----------|
| `wstoken` | string | Token de autenticación | Sí |
| `wsfunction` | string | `local_wb_news_get_news` | Sí |
| `moodlewsrestformat` | string | `json` | Sí |
| `instanceid` | int | ID de la instancia de noticias | Sí |

### Ejemplo con cURL

```bash
curl -X GET "https://tu-dominio.com/webservice/rest/server.php" \
  -d "wstoken=TU_TOKEN_AQUI" \
  -d "wsfunction=local_wb_news_get_news" \
  -d "moodlewsrestformat=json" \
  -d "instanceid=1"
```

### Ejemplo con JavaScript (fetch)

```javascript
const MOODLE_URL = 'https://tu-dominio.com';
const WS_TOKEN = 'TU_TOKEN_AQUI';
const INSTANCE_ID = 1;

async function getNews() {
  const url = new URL(`${MOODLE_URL}/webservice/rest/server.php`);
  url.searchParams.append('wstoken', WS_TOKEN);
  url.searchParams.append('wsfunction', 'local_wb_news_get_news');
  url.searchParams.append('moodlewsrestformat', 'json');
  url.searchParams.append('instanceid', INSTANCE_ID);

  try {
    const response = await fetch(url);
    const data = await response.json();
    
    if (data.success) {
      console.log('Noticias:', data.items);
      return data.items;
    } else {
      console.error('Error:', data.message);
      return [];
    }
  } catch (error) {
    console.error('Error de red:', error);
    return [];
  }
}

// Uso
getNews().then(news => {
  news.forEach(item => {
    console.log(`${item.headline}: ${item.description}`);
  });
});
```

### Ejemplo con Python

```python
import requests

MOODLE_URL = 'https://tu-dominio.com'
WS_TOKEN = 'TU_TOKEN_AQUI'
INSTANCE_ID = 1

def get_news(instance_id):
    url = f'{MOODLE_URL}/webservice/rest/server.php'
    params = {
        'wstoken': WS_TOKEN,
        'wsfunction': 'local_wb_news_get_news',
        'moodlewsrestformat': 'json',
        'instanceid': instance_id
    }
    
    response = requests.get(url, params=params)
    data = response.json()
    
    if data.get('success'):
        return data.get('items', [])
    else:
        print(f"Error: {data.get('message')}")
        return []

# Uso
news = get_news(INSTANCE_ID)
for item in news:
    print(f"{item['headline']}: {item['description']}")
```

## Respuesta del servicio

### Estructura JSON

```json
{
  "items": [
    {
      "id": 1,
      "instanceid": 1,
      "active": 1,
      "imagemode": 1,
      "sortorder": 0,
      "bgimage": "https://tu-dominio.com/pluginfile.php/...",
      "bgimagetext": "Texto alternativo",
      "icon": "https://tu-dominio.com/pluginfile.php/...",
      "icontext": "Icono",
      "bgcolor": "#ffffff",
      "userid": 2,
      "headline": "Título de la noticia",
      "subheadline": "Subtítulo opcional",
      "description": "<p>Descripción formateada en HTML</p>",
      "descriptionformat": 1,
      "btnlink": "https://ejemplo.com",
      "btnlinkattributes": "target=\"_blank\"",
      "btntext": "Leer más",
      "lightmode": 0,
      "cssclasses": "custom-class",
      "json": "{\"extra\":\"data\"}",
      "timecreated": 1699999999,
      "timemodified": 1700000000,
      "author": "Nombre del Autor",
      "authorpicture": "https://tu-dominio.com/pluginfile.php/...",
      "detailurl": "https://tu-dominio.com/local/wb_news/view.php?id=1"
    }
  ],
  "success": true,
  "message": ""
}
```

### Campos de cada noticia

#### Campos de la tabla `local_wb_news`
- **id**: ID único de la noticia
- **instanceid**: ID de la instancia a la que pertenece
- **active**: Estado activo (1) o inactivo (0)
- **imagemode**: Modo de imagen (1=header, 2=background, etc.)
- **sortorder**: Orden de visualización
- **bgimage**: URL de imagen de fondo
- **bgimagetext**: Texto alternativo para imagen de fondo
- **icon**: URL del icono opcional
- **icontext**: Texto alternativo del icono
- **bgcolor**: Color de fondo en formato hexadecimal
- **userid**: ID del usuario autor
- **headline**: Título principal de la noticia
- **subheadline**: Subtítulo opcional
- **description**: Cuerpo de la noticia en HTML
- **descriptionformat**: Formato del texto (1=HTML, 0=Plain, etc.)
- **btnlink**: URL del botón de acción
- **btnlinkattributes**: Atributos HTML del botón (ej: target, rel)
- **btntext**: Texto del botón
- **lightmode**: Variante visual (0=dark, 1=light)
- **cssclasses**: Clases CSS adicionales personalizadas
- **json**: Datos adicionales en formato JSON
- **timecreated**: Timestamp de creación (Unix timestamp)
- **timemodified**: Timestamp de última modificación

#### Campos adicionales (agregados por `return_list_of_news()`)
- **author**: Nombre completo del autor
- **authorpicture**: URL de la foto del autor
- **detailurl**: URL para ver el detalle completo de la noticia

## Seguridad

- **Tokens**: Mantén los tokens seguros, no los expongas en código cliente
- **HTTPS**: Usa siempre HTTPS en producción
- **Capabilities**: El servicio está configurado sin capabilities específicas para acceso público. Si necesitas restringir acceso, modifica `db/services.php` y agrega la capability apropiada

## Troubleshooting

### Error: "Invalid token"
- Verifica que el token sea correcto
- Verifica que el usuario asociado al token tenga permisos

### Error: "Function not found"
- Ejecuta `php admin/cli/upgrade.php` en el servidor
- Verifica que los archivos estén en la ubicación correcta

### Error: "Invalid instance ID"
- Verifica que el `instanceid` exista en la tabla `local_wb_news_instance`
- Consulta la BD: `SELECT * FROM mdl_local_wb_news_instance;`

### No se devuelven noticias
- Verifica que existan noticias: `SELECT * FROM mdl_local_wb_news WHERE instanceid = X;`
- Revisa los logs de Moodle en `/var/www/moodledata/` o en la interfaz web
