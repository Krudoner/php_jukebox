# Cuerpos de prueba de api/catalogo.php

Cuerpos JSON para repetir las pruebas del contrato con curl o Postman. No contienen cookies ni tokens:
la sesión y el token CSRF se obtienen en cada prueba (ver docs/16-alta-ajax.md).

| Archivo | Acción | Resultado esperado |
|---|---|---|
| alta-valida.json | create | 201 y la lista con la ficha nueva |
| alta-duracion-negativa.json | create | 422 · La duración debe ser un entero entre 1 y 1800 segundos. |
| alta-categoria-no-admitida.json | create | 422 · Selecciona una categoría válida. |
| alta-json-mal-formado.json | create | 400 · El cuerpo no es un JSON válido. |
| editar-id-inexistente.json | update | 404 · No existe ninguna canción con ese identificador. |
