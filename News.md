# [Soporte serializacion XML](http://blog.ncdcsolutions.com/index.php/2010/02/02/soporte-de-xml-en-phporm/) #

# Introduccion #

phpORM es una libreria que nos permite el acceso a datos relacionales ( mysql, postgresql, mssql , ... ) usando una notacion totalmente orientada a objetos.


# Details #

**La informacion sobre las columnas de la tabla y sus claves primarias es recolectada automaticamente por la libreria.** Soporte para tablas con claves primarias compuestas.
**Acceso a las columnas se realiza usando las propiedades del objeto del mismo nombre** phpORM lanza excepciones cuando:
 No existe el registro
 Se intenta acceder a una propiedad ( columna ) inexistente
