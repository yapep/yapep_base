<?php

namespace YapepBase\Util;

class CsvParser {
    /**
     * Separator used for separating fields
     * @var      string
     */
    protected $separator = ',';
    /**
     * Delimiter signalling start/end of field. Optional.
     * @var      string
     */
    protected $delimiter = '"';
    /**
     * Escape character used for escaping delimiter within the field.
     * @var      string
     */
    protected $escape    = '\\';

    /**
     * Sets the character, that should be used for delimiting fields.
     * @param    string   $delimiter
     */
    public function setDelimiter($delimiter) {
        $this->delimiter = $delimiter;
    }

    /**
     * Returns the character, that is used for delimiting fields.
     * @return   string
     */
    public function getDelimiter() {
        return $this->delimiter;
    }

    /**
     * Sets the field separator character
     * @param    string   $separator
     */
    public function setSeparator($separator) {
        $this->separator = $separator;
    }

    /**
     * Returns the field separator character
     * @return   string
     */
    public function getSeparator() {
        return $this->separator;
    }

    /**
     * Sets the escape character used to escape literals.
     * @param    string   $escape
     */
    public function setEscape($escape) {
        $this->escape = $escape;
    }

    /**
     * Returns the character used to escape literals
     * @return   string
     */
    public function getEscape() {
        return $this->escape;
    }

    /**
     * Parses a single row of CSV data into a number-indexed array
     * @param    string   $row
     * @return   array
     */
    public function parseRow($row) {
        /**
         * Array of all characters
         */
        $characters   = preg_split('/(.)/', $row, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        /**
         * All already processed fields.
         */
        $fields       = array();
        /**
         * Contents of the last field in processing.
         */
        $lastfield    = '';
        /**
         * We are in a field while processing.
         */
        $infield      = false;
        /**
         * The field exists, though it may be empty.
         */
        $fieldexists  = false;
        /**
         * The field has a delimiter.
         */
        $hasDelimiter = false;
        while (count($characters)) {
            $character            = array_shift($characters);
            if ($infield &&
                $character        == $this->getEscape() &&
                $characters[0]    == $this->getDelimiter()) {
                /**
                 * We are in a field and have found an escape character and the next character is a delimiter.
                 */
                array_shift($characters);
                $lastfield       .= $this->getDelimiter();
            } else if ($character == $this->getDelimiter()) {
                /**
                 * We have encountered a field delimiter
                 */
                if (!$infield) {
                    $infield      = true;
                    $fieldexists  = true;
                    $hasDelimiter = true;
                } else if ($infield && $hasDelimiter) {
                    $infield      = false;
                } else {
                    $lastfield   .= $character;
                }
            } else if ($character == $this->getSeparator() &&
                (!$infield || !$hasDelimiter)) {
                /**
                 * We have encountered a field separator, a new field starts.
                 */
                $fields[]         = $lastfield;
                $lastfield        = '';
                $fieldexists      = false;
                $hasDelimiter     = false;
                $infield          = false;
            } else if (!$infield) {
                /**
                 * We are not in a field and have encountered something else, than a delimiter.
                 */
                $hasDelimiter     = false;
                $infield          = true;
                $lastfield       .= $character;
                $fieldexists      = true;
            } else {
                /**
                 * We are in a field, add the character.
                 */
                $lastfield       .= $character;
            }
        }
        if ($fieldexists) {
            $fields[]             = $lastfield;
        }
        return $fields;
    }

    /**
     * Parses a multiline CSV string
     * @param    string   $string
     * @return   array
     */
    public function parseString($string) {
        $string           = explode("\n", strtr(strtr($string, "\r\n", "\n"), "\r", "\n"));
        $result           = array();
        foreach ($string as $row) {
            if ($row) {
                $result[] = $this->parseRow($row);
            }
        }
        return $result;
    }

    /**
     * Parses a multiline CSV string using the first line as headers for creating associative arrays.
     * @param    string   $string
     * @return   array
     */
    public function parseStringWithHeaders($string) {
        $data = $this->parseString($string);
        if (count($data)) {
            $headers = array_shift($data);
            foreach ($data as $rowno => $row) {
                foreach ($row as $key => $field) {
                    unset($data[$rowno][$key]);
                    $data[$rowno][$headers[$key]] = $field;
                }
            }
        }
        return $data;
    }
}