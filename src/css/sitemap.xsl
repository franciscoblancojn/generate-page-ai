<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
  xmlns:html="http://www.w3.org/TR/REC-html40"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
  xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <title>XML Sitemap</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
          body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 14px;
            color: #333;
            background: #f0f0f1;
            margin: 0;
            padding: 20px;
          }
          #sitemap {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 30px;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
          }
          h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 10px;
            color: #1d2327;
          }
          p {
            color: #50575e;
            margin: 0 0 20px;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
          }
          th {
            text-align: left;
            background: #2271b1;
            color: #fff;
            padding: 10px 12px;
            font-weight: 600;
          }
          td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
          }
          tr:nth-child(even) td {
            background: #f6f7f7;
          }
          tr:hover td {
            background: #e8f0fe;
          }
          a {
            color: #2271b1;
            text-decoration: none;
          }
          a:hover {
            color: #135e96;
            text-decoration: underline;
          }
          .url {
            word-break: break-all;
          }
          .images-list {
            list-style: none;
            margin: 4px 0 0;
            padding: 0;
          }
          .images-list li {
            display: inline-block;
            margin: 2px;
          }
          .images-list img {
            max-width: 60px;
            max-height: 60px;
            border: 1px solid #ddd;
            border-radius: 3px;
          }
          .footer {
            text-align: center;
            color: #646970;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #dcdcde;
          }
        </style>
      </head>
      <body>
        <div id="sitemap">
          <h1>XML Sitemap</h1>
          <p>Generado por <strong>Generate Page AI</strong> — <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> URLs</p>
          <table>
            <tr>
              <th>URL</th>
              <th>Last Modified</th>
              <th>Change Frequency</th>
              <th>Priority</th>
              <xsl:if test="sitemap:urlset/sitemap:url/image:image">
                <th>Images</th>
              </xsl:if>
            </tr>
            <xsl:for-each select="sitemap:urlset/sitemap:url">
              <tr>
                <td class="url">
                  <a href="{sitemap:loc}" target="_blank">
                    <xsl:value-of select="sitemap:loc"/>
                  </a>
                </td>
                <td>
                  <xsl:value-of select="sitemap:lastmod"/>
                </td>
                <td>
                  <xsl:value-of select="sitemap:changefreq"/>
                </td>
                <td>
                  <xsl:value-of select="sitemap:priority"/>
                </td>
                <xsl:if test="../image:image or ancestor::sitemap:urlset/sitemap:url/image:image">
                  <td>
                    <xsl:choose>
                      <xsl:when test="image:image">
                        <ul class="images-list">
                          <xsl:for-each select="image:image">
                            <li>
                              <a href="{image:loc}" target="_blank">
                                <img src="{image:loc}" alt=""/>
                              </a>
                            </li>
                          </xsl:for-each>
                        </ul>
                      </xsl:when>
                      <xsl:otherwise>
                        <span style="color:#999;">—</span>
                      </xsl:otherwise>
                    </xsl:choose>
                  </td>
                </xsl:if>
              </tr>
            </xsl:for-each>
          </table>
          <div class="footer">
            Generate Page AI v<span>2.7.2</span>
          </div>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
