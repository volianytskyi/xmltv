# XML TV Converter

The script just converts the [EPG](https://en.wikipedia.org/wiki/Electronic_program_guide) xml file to the proper [XML TV format](http://wiki.xmltv.org/index.php/XMLTVFormat)

## Getting Started

Copy the [convert_to_xmltv.php](convert_to_xmltv.php) file into your working directory

### Installing

Make sure [PHP XML module](http://php.net/manual/en/book.simplexml.php) is installed on the server or run such line to install:
```
sudo apt-get install php-xml
```

### Usage
The example original.xml file:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<epg generatedAt="2017-10-04T17:01:00Z">
  <meta>
    <channels>
      <channel id="1" name="Channel 1" />
    </channels>
  </meta>
  <programs>
    <program channel="1" startTime="2018-04-19T10:00:00Z" duration="90">
      <contentTypeRefs />
      <descriptions>
        <description lang="en" title="Program 1">
          <synopsis>Description</synopsis>
        </description>
      </descriptions>
    </program>
  </programs>
</epg>
```

The output after running the **php convert_to_xmltv.php original.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<tv generator-info-name="xml tv converter script" generator-info-url="https://github.com/volyanytsky/xmltv">
  <channel id="1">
    <display-name>Channel 1</display-name>
  </channel>
  <programme start="20180419100000 +0300" stop="20180419113000 +0300" channel="1">
    <title lang="en">Program 1</title>
    <desc lang="en">Description</desc>
  </programme>
</tv>
```

## Authors

* **Sergey Volyanytsky** - https://github.com/volyanytsky

## License

This project is licensed under the WTFPL License - see the [LICENSE.md](LICENSE.md) file for details
