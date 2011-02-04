<?xml version="1.0"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output indent="yes" method="html" />
  <xsl:include href="search.xsl" />

  <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <title><xsl:value-of select="$title" /></title>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <link rel="stylesheet" href="{$root}/css/default.css" type="text/css" />
      </head>
      <body>
        <div id="menu">

          <h1>Documentation</h1>
          <ul>
            <li><a href="{$root}/index.html">Class diagram</a></li>
            <li><a href="{$root}/markers.html">TODO / Markers</a></li>
          </ul>
        </div>

        <div id="index">
          <xsl:value-of select="$object-index" disable-output-escaping="yes"/>
        </div>

		<div id="content">
          <xsl:apply-templates />
        </div>

        <div id="footer">
          <h1><img src="{$root}/images/logo.png" alt="" /></h1>
        </div>


      </body>
    </html>

  </xsl:template>


  <xsl:template name="header">
    <xsl:param name="title" />

    <xsl:call-template name="search">
      <xsl:with-param name="search_template" select="$search_template" />
      <xsl:with-param name="root" select="$root" />
    </xsl:call-template>

    <div id="nb-header">
      <xsl:value-of select="$title" />

      <div class="ui-widget" style="display: inline; float: right; font-size: 0.5em; margin-top: 8px;">
        <label for="search_box">Search</label>
        <input id="search_box" style="display: none"/>
      </div>
    </div>
  </xsl:template>

</xsl:stylesheet>