<?php
	class ControllerTest extends PHPUnit_Framework_TestCase
	{
		private $controller;
		private $fileName;

		protected function setup()
		{
			$this->controller = new \Controller();
			$this->fileName = 'efiuihhaeafiun9864029884IJBoizeefiuh_uh';
			touch(PWD_TEMPLATES . '/' . $this->fileName . '.php');
		}

		protected function tearDown()
		{
			unlink(PWD_TEMPLATES . '/' . $this->fileName . '.php');
		}

		public function assertPreConditions()
		{
			$this->assertTrue(file_exists(PWD_TEMPLATES . '/' . $this->fileName . '.php'));
		}

		public function testS()
		{
			$textHtml = "<h1>Mon Text</h1>\n<p>Mon paragraphe <a href=\"http://example.fr\">avec un lien</a>.</p>";

			$textEscape = "&lt;h1&gt;Mon Text&lt;/h1&gt;\n&lt;p&gt;Mon paragraphe &lt;a href=&quot;http://example.fr&quot;&gt;avec un lien&lt;/a&gt;.&lt;/p&gt;";
			$textNl2br = "&lt;h1&gt;Mon Text&lt;/h1&gt;<br />\n&lt;p&gt;Mon paragraphe &lt;a href=&quot;http://example.fr&quot;&gt;avec un lien&lt;/a&gt;.&lt;/p&gt;";
			$textNoEscapeQuotes = "&lt;h1&gt;Mon Text&lt;/h1&gt;\n&lt;p&gt;Mon paragraphe &lt;a href=\"http://example.fr\"&gt;avec un lien&lt;/a&gt;.&lt;/p&gt;";

			$this->assertEquals($this->controller->s($textHtml, false, true, false), $textEscape);
			$this->assertEquals($this->controller->s($textHtml, true, true, false), $textNl2br);
			$this->assertEquals($this->controller->s($textHtml, false, false, false), $textNoEscapeQuotes);
		}
	}
