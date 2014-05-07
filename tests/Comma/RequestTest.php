<?php

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequestMethod()
    {
        $request = new \Comma\Request('GET');
        $this->assertEquals('GET', $request->method());
        $request = null;

        $request = new \Comma\Request('POST');
        $this->assertEquals('POST', $request->method());
        $request = null;

        $request = new \Comma\Request('PUT');
        $this->assertEquals('PUT', $request->method());
        $request = null;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \Comma\Request();
        $this->assertEquals('GET', $request->method());
        $request = null;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new \Comma\Request();
        $this->assertEquals('POST', $request->method());
        $request = null;

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new \Comma\Request();
        $this->assertEquals('PUT', $request->method());
        $request = null;
    }

    public function testServerVars()
    {
        $expected = array(
            'MAIL' => '/var/mail/vagrant',
            'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/opt/vagrant_ruby/bin',
            'PWD' => '/www/comma/tests',
            'LANG' => 'ru_RU.UTF-8',
            'SHLVL' => '1',
            'HOME' => '/home/vagrant',
        );
        $request = new \Comma\Request(null, $expected);
        $this->assertEquals($expected, $request->vars('SERVER'));
        $request = null;

        $request = new \Comma\Request(null, $_SERVER);
        $this->assertEquals($_SERVER, $request->vars('SERVER'));
        $request = null;
    }

    public function testServerVar()
    {
        $data = array(
            'HTTP_HOST' => 'http_host',
            'SERVER_NAME' => 'server_name',
            'LANG' => 'ru_RU.UTF-8',
            'HOME' => '/home/vagrant'
        );
        $request = new \Comma\Request(null, $data);
        $this->assertEquals($data['SERVER_NAME'], $request->server('SERVER_NAME'));
        $this->assertEquals($data['SERVER_NAME'], $request->server('SERVER_NAME', 'default_server_name'));
        $this->assertEquals($data['HOME'], $request->server('HOME'));
        $this->assertEquals('default_value', $request->server('UNDEFINED_VAR', 'default_value'));
        $request = null;

        $_SERVER['MY_VAR'] = 'my_var';
        $request = new \Comma\Request();
        $this->assertEquals('my_var', $request->server('MY_VAR'));
        $this->assertEquals('my_var', $request->server('MY_VAR', 'default_value'));
        $this->assertEquals('default_value', $request->server('UNDEFINED_VAR', 'default_value'));
    }

    public function testServerVarException()
    {
        $this->setExpectedException('\Comma\Exception');

        $request = new \Comma\Request(null, array());
        $request->server('UNDEFINED_VAR');
    }

    public function testGetVars()
    {
        $expected = array(
            'name' => 'James',
            'surname' => 'Bond'
        );
        $request = new \Comma\Request(null, null, $expected);
        $this->assertEquals($expected, $request->vars('GET'));
        $request = null;

        $request = new \Comma\Request();
        $this->assertEquals($_GET, $request->vars('GET'));
    }

    public function testGetVar()
    {
        $expected = array(
            'name' => 'James',
            'surname' => 'Bond'
        );
        $request = new \Comma\Request(null, null, $expected);
        $this->assertEquals($expected['name'], $request->get('name'));
        $this->assertEquals($expected['name'], $request->get('name', 'default_value'));
        $this->assertEquals($expected['surname'], $request->get('surname'));
        $this->assertEquals('default_value', $request->get('undefined_var', 'default_value'));
        $request = null;

        $_GET['name'] = 'Darien';
        $request = new \Comma\Request();
        $this->assertEquals($_GET['name'], $request->get('name'));
        $this->assertEquals($_GET['name'], $request->get('name', 'default_value'));
        $this->assertEquals('default_value', $request->get('undefined_var', 'default_value'));
    }

    public function testGetVarException()
    {
        $this->setExpectedException('\Comma\Exception');

        $request = new \Comma\Request(null, null, array());
        $request->get('undefined_var');
    }

    public function testPostVars()
    {
        $expected = array(
            'name' => 'James',
            'surname' => 'Bond'
        );
        $request = new \Comma\Request(null, null, null, $expected);
        $this->assertEquals($expected, $request->vars('POST'));
        $request = null;

        $request = new \Comma\Request();
        $this->assertEquals($_POST, $request->vars('POST'));
    }

    public function testPostVar()
    {
        $expected = array(
            'name' => 'James',
            'surname' => 'Bond'
        );
        $request = new \Comma\Request(null, null, null, $expected);
        $this->assertEquals($expected['name'], $request->post('name'));
        $this->assertEquals($expected['name'], $request->post('name', 'default_value'));
        $this->assertEquals($expected['surname'], $request->post('surname'));
        $this->assertEquals('default_value', $request->post('undefined_var', 'default_value'));
        $request = null;

        $_POST['name'] = 'Darien';
        $request = new \Comma\Request();
        $this->assertEquals($_POST['name'], $request->post('name'));
        $this->assertEquals($_POST['name'], $request->post('name', 'default_value'));
        $this->assertEquals('default_value', $request->post('undefined_var', 'default_value'));
    }

    public function testPostVarException()
    {
        $this->setExpectedException('\Comma\Exception');

        $request = new \Comma\Request(null, null, array());
        $request->post('undefined_var');
    }

    public function testFiles()
    {
        $data = array(
            'files' => array(
                'name' => array(
                    'file0.txt',
                    'file1.txt',
                    'file2.txt'
                ),
                'type' => array(
                    'text/plain',
                    'text/plain',
                    'text/plain'
                ),
                'tmp_name' => array(
                    '/tmp/blablabla',
                    '/tmp/phpyzZxta',
                    '/tmp/phpn3nopO'
                ),
                'error' => array(
                    0,
                    0,
                    0
                ),
                'size' => array(
                    100000,
                    200000,
                    300000
                )
            ),
            'file' => array(
                'name' => 'file5.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/single_file',
                'error' => 0,
                'size' => 999999
            )
        );
        $expected = array(
            'files' => array(
                array(
                    'name' => 'file0.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/blablabla',
                    'error' => 0,
                    'size' => 100000
                ),
                array(
                    'name' => 'file1.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpyzZxta',
                    'error' => 0,
                    'size' => 200000
                ),
                array(
                    'name' => 'file2.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpn3nopO',
                    'error' => 0,
                    'size' => 300000
                )
            ),
            'file' => array(
                array(
                    'name' => 'file5.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/single_file',
                    'error' => 0,
                    'size' => 999999
                )
            )
        );

        $request = new \Comma\Request(null, null, null, null, $data);
        $this->assertEquals($expected, $request->files());
    }

    public function testFile()
    {
        $data = array(
            'files' => array(
                'name' => array(
                    'file0.txt',
                    'file1.txt',
                    'file2.txt'
                ),
                'type' => array(
                    'text/plain',
                    'text/plain',
                    'text/plain'
                ),
                'tmp_name' => array(
                    '/tmp/blablabla',
                    '/tmp/phpyzZxta',
                    '/tmp/phpn3nopO'
                ),
                'error' => array(
                    0,
                    0,
                    0
                ),
                'size' => array(
                    100000,
                    200000,
                    300000
                )
            ),
            'file' => array(
                'name' => 'file5.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/single_file',
                'error' => 0,
                'size' => 999999
            )
        );
        $expected = array(
            'files' => array(
                array(
                    'name' => 'file0.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/blablabla',
                    'error' => 0,
                    'size' => 100000
                ),
                array(
                    'name' => 'file1.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpyzZxta',
                    'error' => 0,
                    'size' => 200000
                ),
                array(
                    'name' => 'file2.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpn3nopO',
                    'error' => 0,
                    'size' => 300000
                )
            ),
            'file' => array(
                array(
                    'name' => 'file5.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/single_file',
                    'error' => 0,
                    'size' => 999999
                )
            )
        );

        $request = new \Comma\Request(null, null, null, null, $data);
        $this->assertEquals($expected['files'], $request->file('files'));
        $this->assertEquals(array($expected['files'][0]), $request->file('files', 0));
        $this->assertEquals(array($expected['files'][1]), $request->file('files', 1));
        $this->assertEquals(array($expected['files'][2]), $request->file('files', 2));
        $this->assertEquals($expected['file'], $request->file('file'));
        $this->assertEquals(array($expected['file'][0]), $request->file('file', 0));
    }

    public function testFileException()
    {
        $this->setExpectedException('\Comma\Exception');

        $request = new \Comma\Request(null, null, null, null, array());
        $request->file('some_file');
    }

    public function testMethodVars()
    {
        $methods = array(
            'PUT' => array(
                'entry_id' => 123,
                'entry_label' => 'test entry'
            ),
            'DELETE' => array(
                'entry_id' => 555
            )
        );
        $request = new \Comma\Request(null, null, null, null, null, $methods);
        $this->assertEquals($methods['PUT'], $request->vars('PUT'));
        $this->assertEquals($methods['DELETE'], $request->vars('DELETE'));
    }

    public function testMethodVar()
    {
        $methods = array(
            'PUT' => array(
                'entry_id' => 123,
                'entry_label' => 'test entry'
            ),
            'DELETE' => array(
                'entry_id' => 555
            )
        );
        $request = new \Comma\Request(null, null, null, null, null, $methods);
        $this->assertEquals($methods['PUT']['entry_id'], $request->put('entry_id'));
        $this->assertEquals($methods['PUT']['entry_id'], $request->put('entry_id', 'defaut_value'));
        $this->assertEquals($methods['PUT']['entry_label'], $request->put('entry_label'));
        $this->assertEquals('defaut_value', $request->put('undefined_var', 'defaut_value'));
        $this->assertEquals($methods['DELETE']['entry_id'], $request->delete('entry_id'));
        $this->assertEquals($methods['DELETE']['entry_id'], $request->delete('entry_id'));
        $this->assertEquals('defaut_value', $request->delete('undefined_var', 'defaut_value'));
    }
}
