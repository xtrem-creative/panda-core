<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" encoding="utf-8" indent="yes" />

    <xsl:template match="/">
        <xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html>
        </xsl:text>
        <html lang="en">
            <head>
                <title>Home test page</title>
            </head>
            <body>
                <h1>Hello <xsl:value-of select="name" /> !</h1>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>