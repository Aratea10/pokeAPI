# Consulta de Pokémon - PokeAPI
Aplicación mínima en PHP que consulta la [PokeAPI](https://pokeapi.co/) y muestra:
- Nombre
- ID
- Una habilidad
- Imagen del Pokémon

Incluye manejo de errores (404 cuando no existe, fallos de red) y salida escapada para evitar inyecciones en HTML.

## Características clave
- Formulario POST con campo de texto para el nombre.
- Normalización del input: minúsculas y espacios → guiones (ej. “mr mime” → “mr-mime”).
- Petición HTTP con cURL, captura del código de estado y mensajes claros.
- Sprite preferente `sprites.front_default` y fallback a `other.official-artwork.front_default` si falta.
- Primera habilidad disponible mostrada cuando exista.
- Escapado de salida con `htmlspecialchars`.
  
## Requisitos
- PHP 8.x con cURL habilitado

Comprueba tu versión:
```bash
php -v
```

## Ejecutar en local
```bash
php -S localhost:8000
```

Abre en el navegador:
- http://localhost:8000
  
## Uso
1. Escribe el nombre del Pokémon (Pikachu, Charmander, etc.).
2. Pulsa "Consultar".
3. Verás nombre, ID, una habilidad y la imagen si está disponible. 
4. Si no existe, se mostrará "El Pokémon que buscas no existe. Prueba otro nombre.".

## Seguridad y robustez
- Todas las salidas pasan por `htmlspecialchars`.
- Manejo explícito de:
  - 404 (no existe).
  - Errores de red / timeouts
  - Respuestas no JSON
- Normalización del nombre para soportar variantes con espacios.

