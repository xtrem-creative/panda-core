<?php

namespace Panda\component\bundle\plugin\view;

class Breadcrumb extends AbstractViewPlugin
{

    private $_items = array();

    /**
     * Build the breadcrumb from the current items list
     * 
     * @return string
     */
    public function render()
    {
        $breadcrumb = '';

        if (!$this->isEmpty()) {
            $breadcrumb .= '<ol class="breadcrumb">';

            foreach ($this->_items as $item) {
                if ($item['url'] !== null) {
                    $breadcrumb .= '<li><a href="'.$item['url'].'">' . $item['label'] . '</a></li>';
                } else {
                    $breadcrumb .= '<li class="active">' . $item['label'] . '</li>';
                }
            }

            $breadcrumb .= '</ol>';
        }

        return $breadcrumb;
    }

    public function add($label, $url = null)
    {
        if (is_string($label) && !empty($label)) {
            $this->_items[] = array('label' => $label, 'url' => $url);
        } else {
            throw new \InvalidArgumentException('Invalid breadcrumb label "' . (string) $label . '"');
        }
        return $this;
    }

    public function isEmpty()
    {
        return count($this->_items) === 0;
    }
}