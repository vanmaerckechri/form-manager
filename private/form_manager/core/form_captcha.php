<?php

class FormCaptcha
{
    private static $randNumber = null;
    private static $elements = [
        "M73.9,52.6c0,35.4-13.1,54.9-36.2,54.9c-20.3,0-34.1-19-34.4-53.4C3.4,19.2,18.4,0,39.5,0C61.4,0,73.9,19.5,73.9,52.6zM17.4,54.2c0,27,8.3,42.4,21.1,42.4c14.4,0,21.3-16.8,21.3-43.4c0-25.6-6.6-42.4-21.1-42.4C26.4,10.9,17.4,25.9,17.4,54.2z",
        "M35.4,14.9H35L17,24.6l-2.7-10.7L37,1.8h12v104H35.4V14.9z",
        "M4.8,105.8v-8.6l11-10.7C42.4,61.1,54.4,47.7,54.6,32c0-10.6-5.1-20.3-20.6-20.3c-9.4,0-17.3,4.8-22.1,8.8l-4.5-9.9C14.6,4.5,24.8,0,36.8,0c22.4,0,31.8,15.4,31.8,30.2c0,19.2-13.9,34.7-35.8,55.8l-8.3,7.7v0.3h46.7v11.7H4.8z",
        "M8.2,89.6c4,2.6,13.3,6.6,23,6.6c18.1,0,23.7-11.5,23.5-20.2c-0.2-14.6-13.3-20.8-26.9-20.8H20V44.6h7.8C38.1,44.6,51,39.4,51,27c0-8.3-5.3-15.7-18.2-15.7c-8.3,0-16.3,3.7-20.8,6.9L8.3,8c5.4-4,16-8,27.2-8C56,0,65.3,12.2,65.3,24.8c0,10.7-6.4,19.8-19.2,24.5v0.3c12.8,2.6,23.2,12.2,23.2,26.7c0,16.6-13,31.2-37.9,31.2c-11.7,0-21.9-3.7-27-7L8.2,89.6z",
        "M48.3,105.8V77.4H0v-9.3L46.4,1.8h15.2v64.6h14.6v11H61.6v28.3H48.3z M48.3,66.4V31.7c0-5.4,0.2-10.9,0.5-16.3h-0.5c-3.2,6.1-5.8,10.6-8.6,15.4L14.2,66.1v0.3H48.3z",
        "M66.9,13.6H27.2l-4,26.7c2.4-0.3,4.6-0.6,8.5-0.6c8,0,16,1.8,22.4,5.6C62.2,49.9,69,58.9,69,72c0,20.3-16.2,35.5-38.7,35.5c-11.4,0-21-3.2-25.9-6.4l3.5-10.7c4.3,2.6,12.8,5.8,22.2,5.8c13.3,0,24.6-8.6,24.6-22.6c-0.2-13.4-9.1-23-29.9-23c-5.9,0-10.6,0.6-14.4,1.1l6.7-49.9h49.8V13.6z",
        "M64.2,11.7c-2.9-0.2-6.6,0-10.6,0.6c-22.1,3.7-33.8,19.8-36.2,37h0.5c5-6.6,13.6-12,25.1-12c18.4,0,31.4,13.3,31.4,33.6c0,19-13,36.6-34.6,36.6C17.6,107.5,3,90.2,3,63.2c0-20.5,7.4-36.6,17.6-46.9C29.3,7.8,40.8,2.6,53.9,1c4.2-0.6,7.7-0.8,10.2-0.8V11.7z M60.2,71.7c0-14.9-8.5-23.8-21.4-23.8c-8.5,0-16.3,5.3-20.2,12.8c-1,1.6-1.6,3.7-1.6,6.2c0.3,17.1,8.2,29.8,22.9,29.8C52,96.6,60.2,86.6,60.2,71.7z",
        "M72.3,1.8V11L27,105.8H12.5l45.1-92v-0.3H6.7V1.8H72.3z",
        "M3.5,79.4c0-13.1,7.8-22.4,20.6-27.8L24,51C12.5,45.6,7.5,36.6,7.5,27.7C7.5,11.2,21.4,0,39.7,0c20.2,0,30.2,12.6,30.2,25.6c0,8.8-4.3,18.2-17.1,24.3v0.5c13,5.1,21,14.2,21,26.9c0,18.1-15.5,30.2-35.4,30.2C16.6,107.5,3.5,94.6,3.5,79.4z M59.5,78.7c0-12.6-8.8-18.7-22.9-22.7c-12.2,3.5-18.7,11.5-18.7,21.4C17.4,88,25.4,97.3,38.7,97.3C51.4,97.3,59.5,89.4,59.5,78.7z M20.8,26.7c0,10.4,7.8,16,19.8,19.2c9-3,15.8-9.4,15.8-18.9c0-8.3-5-17-17.6-17C27.2,10.1,20.8,17.8,20.8,26.7z",
        "M13,95.8c3,0.3,6.6,0,11.4-0.5c8.2-1.1,15.8-4.5,21.8-10.1C53,79,57.9,69.9,59.8,57.6h-0.5c-5.8,7-14.1,11.2-24.5,11.2C16.2,68.8,4.2,54.7,4.2,37c0-19.7,14.2-37,35.5-37s34.4,17.3,34.4,43.8c0,22.9-7.7,38.9-17.9,48.8c-8,7.8-19,12.6-30.2,13.9c-5.1,0.8-9.6,1-13,0.8V95.8z M18.1,36c0,13,7.8,22.1,20,22.1c9.4,0,16.8-4.6,20.5-10.9c0.8-1.3,1.3-2.9,1.3-5.1c0-17.8-6.6-31.4-21.3-31.4C26.6,10.7,18.1,21.3,18.1,36z"
    ];

	public static function get(\DOMDocument $dom, ?int $length = null): \DOMElement
	{
        static::launch_session();

        $length = $length != null ? $length : 5;
        static::$randNumber = static::rand_nElements(self::$elements, $length);
        $_SESSION['captcha'] = isset( $_SESSION['captcha']) ?  $_SESSION['captcha'] : [];
        $_SESSION['captcha'] = ['result' => static::$randNumber, 'length' => $length];
        return static::build_svg($dom, str_split(static::$randNumber));
	}

    public static function is_validCaptcha(string $captcha): bool
	{
        static::launch_session();

        if (isset($_SESSION['captcha']['result']) && $_SESSION['captcha']['result'] === $captcha)
        {
            return true;
        }
        return false;
	}

	private static function rand_nElements(array $elements, int $n): string
	{	
        if (static::$randNumber != null)
        {
            return static::$randNumber;
        }
        $output = '';
        $max = count($elements) - 1;
        for ($i = $n - 1; $i >= 0; $i--)
        {
            $output .= rand(0, $max);
        }
        return  $output;
	}

    private static function build_svg(\DOMDocument $dom, array $randElements): \DOMElement
	{	
        $width = count($randElements) * 300;
        $attributes = [
            "version" => "1.1",
            "xmlns" => "http://www.w3.org/2000/svg",
            "xmlns:xlink" => "http://www.w3.org/1999/xlink",
            "x" => "0px",
            "y" => "0px",
            "viewBox" => "0 0 " . $width . " 200",
            "style" => "enable-background:new 0 0 " . $width . " 300;",
            "xml:space" => "preserve",
            "width" => "100%",
            "height" => "90px"
        ];

        $svg = $dom->createElement('svg');
        static::set_attributes($svg, $attributes);
        
        for ($i = count($randElements) - 1; $i >= 0; $i--)
        {
            $rotate = rand(-40, 40);
            $g = $dom->createElement('g');
            static::set_attributes($g, [
                "transform" => "translate(" . (($i * 300) + 150 - 35) . ", 55) rotate(" . $rotate . ")"
            ]);
            $svg->appendChild($g);

            $path = $dom->createElement('path');
            static::set_attributes($path, [
                "fill" => "grey",
                "d" => static::$elements[$randElements[$i]]
            ]);
            $g->appendChild($path);
        }
        return  $svg;
	}

    private static function set_attributes(\DOMElement $elem, array $attributes): \DOMElement
    {
        foreach($attributes as $key => $value)
        {
            $elem->setAttribute($key, $value);
        }
        return $elem;
    }

    private static function launch_session(): void
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
    }
}