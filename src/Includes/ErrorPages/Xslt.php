<?php

//declare(strict_types=1);

use Symphony\Symphony;

$output = new Symphony\HtmlPage;

$output->Html->setElementStyle('html');

$output->Html->setDTD('<!DOCTYPE html>');
$output->Html->setAttribute('lang', 'en');
$output->addElementToHead(new Symphony\XmlElement('meta', null, array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8')), 0);
$output->addStylesheetToHead(ASSETS_URL.'/css/symphony.min.css', 'screen', null, false);

$output->setHttpStatus($e->getHttpStatusCode());
$output->addHeaderToPage('Content-Type', 'text/html; charset=UTF-8');
$output->addHeaderToPage('Symphony-Error-Type', 'xslt');

$output->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('XSLT Processing Error'))));
$output->Body->setAttribute('id', 'error');

$div = new Symphony\XmlElement('div', null, array('class' => 'frame'));
$ul = new Symphony\XmlElement('ul');
$li = new Symphony\XmlElement('li');
$li->appendChild(new Symphony\XmlElement('h1', __('XSLT Processing Error')));
$li->appendChild(new Symphony\XmlElement('p', __('This page could not be rendered due to the following XSLT processing errors:')));
$ul->appendChild($li);

$errors_grouped = [];

list($key, $val) = $e->getAdditional()->proc->getError(false, true);

do {
    if (preg_match('/^loadXML\(\)/i', $val['message']) && preg_match_all('/line:\s+(\d+)/i', $val['message'], $matches)) {
        $errors_grouped['xml'][] = array('line' => $matches[1][0], 'raw' => $val);
    } elseif (preg_match_all('/pages\/([^.\/]+\.xsl)\s+line\s+(\d+)/i', $val['message'], $matches) || preg_match_all('/pages\/([^.\/]+\.xsl):(\d+):/i', $val['message'], $matches)) {
        $errors_grouped['page'][$matches[1][0]][] = array('line' => $matches[2][0], 'raw' => $val);
    } elseif (preg_match_all('/utilities\/([^.\/]+\.xsl)\s+line\s+(\d+)/i', $val['message'], $matches)) {
        $errors_grouped['utility'][$matches[1][0]][] = array('line' => $matches[2][0], 'raw' => $val);
    } else {
        $val['parts'] = explode(' ', $val['message'], 3);
        $errors_grouped['general'][] = $val;
    }
} while (list($key, $val) = $e->getAdditional()->proc->getError());

$query_string = General::sanitize($output->__buildQueryString());

if (strlen(trim($query_string)) > 0) {
    $query_string = "&amp;{$query_string}";
}

foreach ($errors_grouped as $group => $data) {
    switch ($group) {
        case 'general':
            $error = new Symphony\XmlElement('li', '<header class="frame-header">'.__('General').'<a class="debug" href="?debug'.$query_string.'" title="'.__('Show debug view').'">'.__('Debug').'</a></header>');
            $content = new Symphony\XmlElement('div', null, array('class' => 'content'));
            $list = new Symphony\XmlElement('ul');
            $file = null;
            $line = null;

            foreach ($data as $index => $e) {
                // Highlight error
                $class = [];
                if (false !== strpos($data[$index + 1]['message'], '^')) {
                    $class = array('class' => 'error');
                }

                // Don't show markers
                if (false === strpos($e['message'], '^')) {
                    $parts = explode('(): ', $e['message']);

                    // Function
                    preg_match('/(.*)\:(\d+)\:/', $e['parts'][1], $current);
                    if ($data[$index - 1]['parts'][0] != $e['parts'][0] || (false !== strpos($data[$index - 1]['message'], '^') && $data[$index - 2]['message'] != $data[$index + 1]['message'])) {
                        $list->appendChild(
                            new Symphony\XmlElement(
                                'li',
                                '<code><em>'.$e['parts'][0].' '.$current[1].'</em></code>'
                            )
                        );
                    }

                    // Store current file and line
                    if (count($current) > 2) {
                        $file = $current[1];
                        $line = $current[2];
                    }

                    // Error
                    if (!empty($class)) {
                        if (isset($data[$index + 3]) && !empty($parts[1]) && false === strpos($data[$index + 3]['message'], $parts[1])) {
                            $position = explode('(): ', $data[$index + 1]['message']);
                            $length = max(0, strlen($position[1]) - 1);
                            $list->appendChild(
                                new Symphony\XmlElement(
                                    'li',
                                    '<code>&#160;&#160;&#160;&#160;'.str_replace(' ', '&#160;', trim(htmlspecialchars(substr($parts[1], 0, $length))).'<b>'.htmlspecialchars(substr($parts[1], $length, 1)).'</b>'.htmlspecialchars(substr($parts[1], $length + 1))).'</code>',
                                    $class
                                )
                            );

                            if (isset($file, $line)) {
                                // Show in debug
                                $filename = explode(WORKSPACE.'/', $file);
                                $list->appendChild(
                                    new Symphony\XmlElement(
                                        'li',
                                        '<code>&#160;&#160;&#160;&#160;<a href="?debug=/workspace/'.$filename[1].'#line-'.$line.'" title="'.__('Show debug view for %s', array($filename[1])).'">'.__('Show line %d in debug view', array($line)).'</a></code>'
                                    )
                                );
                            }
                        }

                        // Message
                    } else {
                        $list->appendChild(
                            new Symphony\XmlElement(
                                'li',
                                '<code>&#160;&#160;&#160;&#160;'.(0 !== strpos($e['parts'][1], '/') ? $e['parts'][1].' ' : '').str_replace(' ', '&#160;', $e['parts'][2]).'</code>'
                            )
                        );
                    }
                }
            }

            $content->appendChild($list);
            $error->appendChild($content);
            $ul->appendChild($error);

            break;
        case 'page':
            foreach ($data as $filename => $errors) {
                $error = new Symphony\XmlElement('li', '<header class="frame-header">'.$filename.'<a class="debug" href="?debug=/workspace/pages/'.$filename.$query_string.'" title="'.__('Show debug view').'">'.__('Debug').'</a></header>');
                $content = new Symphony\XmlElement('div', null, array('class' => 'content'));
                $list = new Symphony\XmlElement('ul');

                foreach ($errors as $e) {
                    if (!is_array($e)) {
                        continue;
                    }

                    $parts = explode('(): ', $e['raw']['message']);

                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code><em>'.$parts[0].'():</em></code>'
                        )
                    );
                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code>&#160;&#160;&#160;&#160;'.$parts[1].'</code>'
                        )
                    );
                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code>&#160;&#160;&#160;&#160;<a href="?debug=/workspace/pages/'.$filename.$query_string.'#line-'.$e['line'].'" title="'.__('Show debug view for %s', array($filename)).'">'.__('Show line %d in debug view', array($e['line'])).'</a></code>'
                        )
                    );
                }

                $content->appendChild($list);
                $error->appendChild($content);
                $ul->appendChild($error);
            }

            break;
        case 'utility':
            foreach ($data as $filename => $errors) {
                $error = new Symphony\XmlElement('li', '<header class="frame-header">'.$filename.'<a class="debug" href="?debug=/workspace/utilities/'.$filename.$query_string.'" title="'.__('Show debug view').'">'.__('Debug').'</a></header>');
                $content = new Symphony\XmlElement('div', null, array('class' => 'content'));
                $list = new Symphony\XmlElement('ul');

                foreach ($errors as $e) {
                    if (!is_array($e)) {
                        continue;
                    }

                    $parts = explode('(): ', $e['raw']['message']);

                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code><em>'.$parts[0].'():</em></code>'
                        )
                    );
                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code>&#160;&#160;&#160;&#160;'.$parts[1].'</code>'
                        )
                    );
                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code>&#160;&#160;&#160;&#160;<a href="?debug=/workspace/utilities/'.$filename.$query_string.'#line-'.$e['line'].'" title="'.__('Show debug view for %s', array($filename)).'">'.__('Show line %d in debug view', array($e['line'])).'</a></code>'
                        )
                    );
                }

                $content->appendChild($list);
                $error->appendChild($content);
                $ul->appendChild($error);
            }

            break;
        case 'xml':
            foreach ($data as $filename => $errors) {
                $error = new Symphony\XmlElement('li', '<header class="frame-header">XML <a class="button" href="?debug=xml'.$query_string.'" title="'.__('Show debug view').'">'.__('Debug').'</a></header>');
                $content = new Symphony\XmlElement('div', null, array('class' => 'content'));
                $list = new Symphony\XmlElement('ul');

                foreach ($errors as $e) {
                    if (!is_array($e)) {
                        continue;
                    }

                    $parts = explode('(): ', $e['message']);

                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code><em>'.$parts[0].'():</em></code>'
                        )
                    );
                    $list->appendChild(
                        new Symphony\XmlElement(
                            'li',
                            '<code>&#160;&#160;&#160;&#160;'.$parts[1].'</code>'
                        )
                    );

                    if (false !== strpos($e['file'], WORKSPACE)) {
                        // The line in the exception is where it was thrown, it's
                        // useless for the ?debug view. This gets the line from
                        // the ?debug page.
                        preg_match('/:\s(\d+)$/', $parts[1], $line);

                        $list->appendChild(
                            new Symphony\XmlElement(
                                'li',
                                '<code>&#160;&#160;&#160;&#160;<a href="?debug=xml'.$query_string.'#line-'.$line[1].'" title="'.__('Show debug view for %s', array($filename)).'">'.__('Show line %d in debug view', array($line[1])).'</a></code>'
                            )
                        );
                    } else {
                        $list->appendChild(
                            new Symphony\XmlElement(
                                'li',
                                '<code>&#160;&#160;&#160;&#160;'.$e['file'].':'.$e['line'].'</code>'
                            )
                        );
                    }
                }

                $content->appendChild($list);
                $error->appendChild($content);
                $ul->appendChild($error);
            }

            break;
    }
}

$div->appendChild($ul);
$output->Body->appendChild($div);

echo $output->generate();

exit(1);