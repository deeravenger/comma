<?php

class ViewTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider testAssignProvider
     */
    public function testAssign1($expected)
    {
        $view = new \Comma\View('path', $expected);
        $reflection = new \ReflectionClass($view);
        $property = $reflection->getProperty('_data');
        $property->setAccessible(true);
        $actual = $property->getValue($view);
        $this->assertEquals($actual, $expected);
    }

    /**
     * @dataProvider testAssignProvider
     */
    public function testAssign2($expected)
    {
        $view = new \Comma\View('path');
        foreach ($expected as $key => $value) {
            $view->assign($key, $value);
        }
        $reflection = new \ReflectionClass($view);
        $property = $reflection->getProperty('_data');
        $property->setAccessible(true);
        $actual = $property->getValue($view);
        $this->assertEquals($actual, $expected);
    }

    public function testAssignProvider()
    {
        $data = array();
        $data[] = array(
            'expected' => array(
                'name' => 'Darien',
                'surname' => 'Fawkes',
                'role' => 'Invisible Man'
            )
        );

        return $data;
    }

    public function testSetTemplate()
    {
        $expected = 'path/to/template.php';
        $view = new \Comma\View($expected);
        $reflection = new \ReflectionClass($view);
        $property = $reflection->getProperty('_path');
        $property->setAccessible(true);
        $actual = $property->getValue($view);
        $this->assertEquals($actual, $expected);
    }

    public function testRender()
    {
        $path = dirname(__FILE__) . '/../data/ViewTest_Template.php';

        $view = new \Comma\View($path);
        $view->assign('name', 'Darien')
            ->assign('surname', 'Fawkes')
            ->assign('role', 'Invisible Man');

        $expected = 'I think that Darien Fawkes is "Invisible Man"!';
        $actual = $view->render();
        $this->assertEquals($expected, $actual);
    }

    public function testToString()
    {
        $path = dirname(__FILE__) . '/../data/ViewTest_Template.php';

        $view = new \Comma\View($path);
        $view->assign('name', 'Darien')
            ->assign('surname', 'Fawkes')
            ->assign('role', 'Invisible Man');

        $expected = 'I think that Darien Fawkes is "Invisible Man"!';
        $actual = (string)$view;
        $this->assertEquals($expected, $actual);
    }
}
