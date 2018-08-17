<?php

class ResponseTest extends PHPUnit_Framework_TestCase
{

    public function testCharset()
    {
        $expected = 'windows-1251';
        $response = new \Comma\Response($expected);

        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('_charset');
        $property->setAccessible(true);
        $actual = $property->getValue($response);

        $this->assertEquals($expected, $actual);
    }

    public function testHeaders()
    {
        $expected = array(
            'MyHeader1: Value',
            'MyHeader2: Value',
            'MyHeader3: Value'
        );
        $response = new \Comma\Response();
        foreach ($expected as $header) {
            $response->setHeader($header);
        }

        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('_headers');
        $property->setAccessible(true);
        $actual = $property->getValue($response);

        $this->assertEquals($expected, $actual);
    }

    public function testHtml()
    {
        $expectedHeaders = array('Content-Type: text/html; charset=utf-8');
        $expectedContent = '<html><head><title>Test</title></head><body/></html>';
        $response = new \Comma\Response();
        $response->html($expectedContent);

        $reflection = new \ReflectionClass($response);
        $propertyHeaders = $reflection->getProperty('_headers');
        $propertyHeaders->setAccessible(true);
        $actualHeaders = $propertyHeaders->getValue($response);
        $this->assertEquals($expectedHeaders, $actualHeaders);

        $propertyContent = $reflection->getProperty('_content');
        $propertyContent->setAccessible(true);
        $actualContent = $propertyContent->getValue($response);
        $this->assertEquals($expectedContent, $actualContent);
    }

    public function testText()
    {
        $expectedHeaders = array('Content-Type: text/plain; charset=utf-8');
        $expectedContent = 'Test content!';
        $response = new \Comma\Response();
        $response->text($expectedContent);

        $reflection = new \ReflectionClass($response);
        $propertyHeaders = $reflection->getProperty('_headers');
        $propertyHeaders->setAccessible(true);
        $actualHeaders = $propertyHeaders->getValue($response);
        $this->assertEquals($expectedHeaders, $actualHeaders);

        $propertyContent = $reflection->getProperty('_content');
        $propertyContent->setAccessible(true);
        $actualContent = $propertyContent->getValue($response);
        $this->assertEquals($expectedContent, $actualContent);
    }

    public function testJson()
    {
        $expectedHeaders = array('Content-Type: application/json; charset=utf-8');
        $expectedContent = array('success' => true, 'data' => array(1, 2, 3));
        $response = new \Comma\Response();
        $response->json($expectedContent);

        $reflection = new \ReflectionClass($response);
        $propertyHeaders = $reflection->getProperty('_headers');
        $propertyHeaders->setAccessible(true);
        $actualHeaders = $propertyHeaders->getValue($response);
        $this->assertEquals($expectedHeaders, $actualHeaders);

        $propertyContent = $reflection->getProperty('_content');
        $propertyContent->setAccessible(true);
        $actualContent = $propertyContent->getValue($response);
        $this->assertEquals(json_encode($expectedContent), $actualContent);
    }

    public function testXml()
    {
        $expectedHeaders = array('Content-Type: application/xml; charset=utf-8');
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'utf-8');

        $writer->startElement('root');
        $writer->startElement('name');
        $writer->writeAttribute('age', 28);
        $writer->writeAttribute('role', 'Invisible Man');
        $writer->writeRaw('Darien');
        $writer->endElement();

        $writer->startElement('about');
        $writer->writeCdata('About Invisible Man.');
        $writer->endElement();

        $writer->writeElement('date', '10.11.2013 14:15');

        $writer->endElement();
        $expectedContent = $writer->outputMemory(true);
        $response = new \Comma\Response();
        $response->xml(array(
            'root' => array(
                'name' => array(
                    '@value' => 'Darien',
                    '@attrs' => array(
                        'age' => 28,
                        'role' => 'Invisible Man'
                    )
                ),
                'about' => array(
                    '@cdata' => 'About Invisible Man.'
                ),
                'date' => '10.11.2013 14:15'
            )
        ));

        $reflection = new \ReflectionClass($response);
        $propertyHeaders = $reflection->getProperty('_headers');
        $propertyHeaders->setAccessible(true);
        $actualHeaders = $propertyHeaders->getValue($response);
        $this->assertEquals($expectedHeaders, $actualHeaders);

        $propertyContent = $reflection->getProperty('_content');
        $propertyContent->setAccessible(true);
        $actualContent = $propertyContent->getValue($response);
        $this->assertEquals($expectedContent, $actualContent);
    }

    public function testXmlException()
    {
        $this->setExpectedException('\Comma\Exception');
        $response = new \Comma\Response();
        $response->xml(array(
            'name' => array(
                '@value' => 'Darien',
                '@attrs' => array(
                    'age' => 28,
                    'role' => 'Invisible Man'
                )
            ),
            'about' => array(
                '@cdata' => 'About Invisible Man.'
            ),
            'date' => '10.11.2013 14:15'
        ));
    }

    public function testView()
    {
        $path = dirname(__FILE__) . '/../data/ViewTest_Template.php';

        $view = new \Comma\View($path);
        $view->assign('name', 'Darien')
            ->assign('surname', 'Fawkes')
            ->assign('role', 'Invisible Man');

        $expectedHeaders = array('Content-Type: text/html; charset=utf-8');
        $expectedContent = 'I think that Darien Fawkes is "Invisible Man"!';

        $response = new \Comma\Response();
        $response->view($view);

        $reflection = new \ReflectionClass($response);
        $propertyHeaders = $reflection->getProperty('_headers');
        $propertyHeaders->setAccessible(true);
        $actualHeaders = $propertyHeaders->getValue($response);
        $this->assertEquals($expectedHeaders, $actualHeaders);

        $propertyContent = $reflection->getProperty('_content');
        $propertyContent->setAccessible(true);
        $actualContent = $propertyContent->getValue($response);
        $this->assertEquals($expectedContent, $actualContent);
    }

    public function testContent()
    {
        $content = 'Test content!';
        $response = new \Comma\Response();
        $response->text($content);
        $this->assertEquals($content, $response->getContent());
    }
}
