<?php

namespace App\Services;

/**
 * 
 * @author rendix2
 * @package App\Services
 *
 */
class BBCode
{
    /**
     * 
     * @var resource $bbCode
     */
    private $bbCode;
    
    /**
     * 
     * @param array $bbcode_initial_tags
     */
    public function __construct(array $bbcode_initial_tags = [])
    {
        if (!extension_loaded ('bbcode')) {
            throw new \Exception('BBCode extension is not installed.');
        }
        
        $this->bbCode = bbcode_create($bbcode_initial_tags);
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        bbcode_destroy($this->bbCode);
        $this->bbCode = null;
    }
    
    /**
     * 
     * @param string $tag_name
     * @param array  $tag_rules
     *
     * @return boolean
     */
    public function addElement($tag_name, array $tag_rules)
    {
        return bbcode_add_element($this->bbCode, $tag_name, $tag_rules);
    }
    
    /**
     * 
     * @param string $smiley
     * @param string $replace_by
     *
     * @return boolean
     */
    public function addSmiley($smiley, $replace_by)
    {
        return bbcode_add_smiley($this->bbCode, $smiley, $replace_by);
    }
    
    /**
     * 
     * @param BBCode $bbCode
     *
     * @return boolean
     */
    public function addParser(BBCode $bbCode)    
    {
        return bbcode_set_arg_parser($this->bbCode, $bbCode->getResource());
    }

    /**
     * 
     * @param int $flags
     * @param int $mode
     *
     * @return boolean
     */
    public function setFlags($flags, $mode = BBCODE_SET_FLAGS_SET)
    {
        return bbcode_set_flags($this->bbCode, $flags, $mode);
    }
    
    /**
     * 
     * @param string $to_parse
     *
     * @return string
     */
    public function parse($to_parse)
    {
       return bbcode_parse($this->bbCode, $to_parse);
    }
    
    /**
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->bbCode;
    }
}