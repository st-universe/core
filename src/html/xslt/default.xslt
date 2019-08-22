<?xml version="1.0" encoding="UTF-8"?> 
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:stu="stu" version="1.0" exclude-result-prefixes="stu"> 

	<xsl:output omit-xml-declaration="yes" method="xml" />
    <xsl:template match="stu:root">
        <html lang="de">
            <head>
                <xsl:apply-templates match="stu:siteheader" />
            </head>
        </html>
    </xsl:template>

	<xsl:template match="stu:siteheader">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="stu:body">
		<body onload="{@onload}">
                       	<xsl:apply-templates />
		</body>
	</xsl:template>

	<xsl:template match="stu:header">
		<div style="height: 80px; width: 100%; position: fixed; top: 0; left: 0; background-color: #121220; z-Index: 111111111">
                       	<xsl:apply-templates />
			<div style="margin: 70px 199px 0 101px; height: 10px; width: auto; border-width: 1px; border-bottom: 0; border-style: solid; border-color: #4b4b4b; background-color: #000000; border-radius: 3px 3px 0 0;">
				<xsl:text> </xsl:text>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="stu:sidebar_left">
		<div style="width: 100px; position: fixed; top: 80px; background-color: #121220; height: 100%; border-right: 1px solid #4b4b4b">
                       	<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="stu:sidebar_right">
		<div style="width: 200px ; position: fixed; top: 80px; right: 0; background-color: #121220; height: 100%; border-left: 1px solid #4b4b4b">
			<div style="width: auto; margin-left: 20px; margin-right: 20px">
                       		<xsl:apply-templates />
			</div>
		</div>
	</xsl:template>

	<xsl:template match="stu:content">
		<div style="width: auto; margin: 75px 200px 0 100px;">
                       	<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="stu:content_inner">
		<div style="width: 100%;  padding: 5px 5px 0 5px">
                       	<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="stu:navigation_item">
		<xsl:variable name="linkid"><xsl:value-of select="generate-id()" /></xsl:variable>
		<div class="navigation_item border_box" onclick="goToUrl('{@url}')" onmouseover="cp('{$linkid}','buttons/menu_{@icon_url}1')" onmouseout="cp('{$linkid}','buttons/menu_{@icon_url}0')">
			<img src="assets/buttons/menu_{@icon_url}0.gif" height="30" width="30" id="{$linkid}" />
			<div style="margin-top: 5px"><xsl:value-of select="@title" /></div>
		</div>
	</xsl:template>

	<xsl:template match="stu:jsimagelink">
		<xsl:variable name="linkid"><xsl:value-of select="generate-id()" /></xsl:variable>
		<a href="javascript:void(0);" onclick="{@onclick}" title="{@title}" onmouseover="cp('{$linkid}','buttons/{@image}2')" onmouseout="cp('{$linkid}','buttons/{@image}1')"><img src="assets/buttons/{@image}1.gif" name="{$linkid}" /><xsl:if test="@description" >&#xA0;<xsl:value-of select="@description" /></xsl:if></a>
	</xsl:template>
	<xsl:template match="stu:imagelink">
		<xsl:variable name="linkid"><xsl:value-of select="generate-id()" /></xsl:variable>
		<xsl:variable name="title">
			<xsl:choose>
				<xsl:when test="@title"><xsl:value-of select="@title" /></xsl:when>
				<xsl:otherwise><xsl:value-of select="@description" /></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<a href="{@href}" onmouseover="cp('{$linkid}','buttons/{@image}2')" onmouseout="cp('{$linkid}','buttons/{@image}1')">
			<img src="assets/buttons/{@image}1.gif" name="{$linkid}" title="{$title}" />
			<xsl:if test="@description">&#xA0;<xsl:value-of select="@description" /></xsl:if>
		</a>
	</xsl:template>
	<xsl:template match="stu:revimagelink">
		<xsl:variable name="linkid"><xsl:value-of select="generate-id()" /></xsl:variable>
		<a href="{@href}" onmouseover="cp('{$linkid}','buttons/{@image}1')" onmouseout="cp('{$linkid}','buttons/{@image}2')">
			<img src="assets/buttons/{@image}2.gif" name="{$linkid}" />&#xA0;<xsl:value-of select="@description" />
		</a>
	</xsl:template>
	<xsl:template match="stu:textarea">
		<textarea style="{@style}" name="{@name}" id="{@name}">
			<xsl:apply-templates />
		</textarea>
	</xsl:template>

	<xsl:template match="stu:textinput">
		<xsl:variable name="value">
			<xsl:choose>
				<xsl:when test="@value">
					<xsl:value-of select="@value" />
				</xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<input type="text" size="{@width}" name="{@name}" value="{$value}" />
	</xsl:template>
	<xsl:template match="stu:hidden">
		<input type="hidden" id="{@name}" name="{@name}" value="{@value}" />
	</xsl:template>
	<xsl:template match="stu:radio">
		<input type="radio" name="{@name}" value="{@value}">
			<xsl:if test="@checked">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
		</input>
	</xsl:template>	
	<xsl:template match="stu:submit">
		<input type="submit" style="cursor: pointer;" name="{@name}" value="{@value}" />
	</xsl:template>
	<xsl:template match="stu:goodentry">
		<xsl:variable name="id"><xsl:value-of select="generate-id()" /></xsl:variable>
		<table style="width: 100%;">
			<tr>
				<td style="width: 20px;">
					<img src="assets/goods/{@goodid}.gif" onclick="$('{$id}').value={@count}" style="cursor: pointer;" title="{@name}" />
				</td>
				<td style="width: 60px; vertical-align: middle;">
					<xsl:value-of select="@count" /> 
				</td>
				<td>
					<input type="text" size="3" id="{$id}" name="count[]" />
				</td>
			</tr>
		</table>
	</xsl:template>
	<xsl:template match="stu:box">
		<xsl:variable name="style">
			<xsl:choose>
				<xsl:when test="@style">
					<xsl:value-of select="@style" />
				</xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<div class="box">
			<div class="box_title">
				<xsl:value-of select="@title" />
			</div>
			<div class="box_body" style="{$style}">
				<xsl:apply-templates />
			</div>
		</div>
	</xsl:template>

	<!-- copy html -->
        <xsl:template match="@*|node()">
                <xsl:copy>
                        <xsl:apply-templates select="@*|node()"/>
                </xsl:copy>
        </xsl:template>
</xsl:stylesheet>
