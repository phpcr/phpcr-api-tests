<?php
class jr_cr_property extends jr_cr_item implements PHPCR_PropertyInterface {
    /**
     * @var jr_cr_node
     */
    protected $parentNode = null;
    protected $path = null;
    protected $type = null;
    public $JRprop = null;
    protected $value = null;
    protected $values = null;

    /**
     * @param jr_cr_node $parentNode
     * @param java $jrprop
     */
    public function __construct($parentNode, $jrprop) {
        parent::__construct($parentNode->getSession(), $jrprop);
        $this->JRprop = $jrprop;
        $this->parentNode = $parentNode;
    }

    /**
     *
     * @see Value::getBoolean()
     * @return bool
     * A boolean representation of the value of this {@link Property}.
     * @see PHPCR_Property::getBoolean()
     */
    public function getBoolean() {
        return $this->getValue()->getBoolean();
    }

    /**
     * If this property is of type REFERENCE, WEAKREFERENCE or PATH (or
     * convertible to one of these types) this method returns the Node to which
     * this property refers.
     *
     * @see PHPCR_Property::getNode
     */
    public function getNode() {
        try {
            return new jr_cr_node($this->session, $this->JRprop->getNode());
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 2);
            if (false !== strpos($str[0], 'ValueFormatException')) {
                throw new PHPCR_ValueFormatException($e->getMessage());
            } elseif (false !== strpos($str[0], 'ItemNotFoundException')) {
                throw new PHPCR_ItemNotFoundException($e->getMessage());
            } else {
                throw new PHPCR_RepositoryException($e->getMessage());
            }
        }
    }

    /**
     *
     * @see Value, Value::getDate()
     * @return DateTime
     * A date representation of the value of this {@link Property}.
     * @see PHPCR_Property::getDate()
     */
    public function getDate() {
        return $this->getValue()->getDate();
    }

    /**
     *
     * @see NodeType::getPropertyDefinitions()
     * @return object
     * A {@link PropertyDef} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getDefinition()
     */
    public function getDefinition() {
        return    $this->JRprop->getDefinition(); //FIXME: wrap into php object!
    }

    /**
     * PHP does not distinct between float and double.
     *
     * @return float
     * @see PHPCR_Property::getDouble()
     */
    public function getDouble() {
        return $this->getValue()->getDouble();
    }

    /**
     *
     * @return int
     * @see PHPCR_Property::getLength()
     */
    public function getLength() {
        try {
            $length = $this->JRprop->getLength();
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 2);
            if (false !== strpos($str[0], 'ValueFormatException')) {
                throw new PHPCR_ValueFormatException($e->getMessage());
            } else {
                throw new PHPCR_RepositoryException($e->getMessage());
            }
        }
        return $length;
    }

    /**
     *
     * @return array
     * @see PHPCR_Property::getLengths()
     */
    public function getLengths() {
        try {
            $lengths = $this->JRprop->getLengths();
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 2);
            if (false !== strpos($str[0], 'ValueFormatException')) {
                throw new PHPCR_ValueFormatException($e->getMessage());
            } else {
                throw new PHPCR_RepositoryException($e->getMessage());
            }
        }
        return $lengths;
    }

    /**
     *
     * @see Value::getLong()
     * @return int
     * An integer representation of the value of this {@link Property}.
     * @see PHPCR_Property::getLong()
     */
    public function getLong() {
        return $this->getValue()->getLong();
    }

    /**
     *
     * @see Value
     * @return string
     * A string representation of the value of this {@link Property}.
     * @see PHPCR_Property::getString()
     */
    public function getString() {
        //TODO: this should actually return something like $jrproperty->getValue->getString();
        $cacheKey = md5("prop::getString::".$this->getPath());
        if (!($this->session->cache && $result = $this->session->cache->load($cacheKey))) {
            if ($this->getType() == PHPCR_PropertyType::BINARY) {

                /**
                 * the copyToFile() method is a patch for
                 *   jackrabbit-jcr-rmi/src/main/java/org/apache/jackrabbit/rmi/client/ClientProperty.java
                 *  which allows to put the content of a Binary Value to a file, which then can
                 *  be read by PHP. See also ClientProperty.java.patch
                 *
                 * If anyone has a fast and better way, without having to patch ClientProperty.java, tell me :)
                 *
                 * If copyToFile doesn't exist, use a muchmuch slower method
                 */
                try {
                    $filename = tempnam(sys_get_temp_dir(), "jrcr2");
                    $this->JRprop->copyToFile($filename);
                    $data = file_get_contents($filename);
                } catch (Exception $e) {
                    $in = $this->JRprop->getBinary();
                    $data = "";
                    while (($len = $in->read()) != - 1) {
                        //$out->write($len);
                        if ($len < 0) {
                            $data .= chr($len + 256);
                        } else {
                            $data .= chr($len);
                        }
                    }
                    if ($this->session->cache) {
                        $this->session->cache->save($data,$cacheKey,array(md5($this->getParent()->getPath())));
                    }
                    return $data;
                    /* another way, to be benchmarked...

                        $out = new Java("java.io.FileOutputStream", $filename);
                    while (($len = $in->read())  != -1) {
                        $out->write($len);
                    }
                    $out->close();
                    */
                }
                if ($filename) {
                    unlink($filename);
                }
                $this->session->cache->save($data,$cacheKey,array(md5($this->getPath())));
                return $data;
            }
            $data = (string) $this->JRprop->getString();
            // $this->session->cache->save($data,$cacheKey,array(md5($this->getPath())));
            return $data;
        }
        return $result;
    }

    /**
     *
     * @return int
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Property::getType()
     */
    public function getType() {
        if (!$this->type) {
            $this->type =  $this->JRprop->getType();
        }
        return $this->type;
    }

    /**
     *
     * @return object
     * @throws {@link ValueFormatException}
     * If the property is multi-valued.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getValue()
     */
    public function getValue() {
        if (null === $this->value)  {
            try {
                $value = $this->JRprop->getValue();
            } catch (JavaException $e) {
                $str = split("\n", $e->getMessage(), 2);
                if (false !== strpos($str[0], 'ValueFormatException')) {
                    throw new PHPCR_ValueFormatException($e->getMessage());
                } else {
                    throw new PHPCR_RepositoryException($e->getMessage());
                }
            }
            $this->value = new jr_cr_value($value);
        }
        return $this->value;
    }

    /**
     *
     * @return array
     * An array of {@link Value}s.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getValues()
     */
    public function getValues() {
        if (null === $this->values) {
            try {
                $values = $this->JRprop->getValues();
            } catch (JavaException $e) {
                $str = split("\n", $e->getMessage(), 2);
                if (false !== strpos($str[0], 'ValueFormatException')) {
                    throw new PHPCR_ValueFormatException($e->getMessage());
                } else {
                    throw new PHPCR_RepositoryException($e->getMessage());
                }
            }

            $this->values = array();
            foreach ($values as $value) {
                array_push($this->values, new jr_cr_value($value));
            }
        }
        return $this->values;
    }

    /**
     *
     * @param mixed
     *   The new value to set the {@link Property} to.
     * @throws {@link ValueFormatException}
     * If the type or format of the specified value is incompatible with the
     * type of this {@link Property}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Property::setValue()
     */
    public function setValue($value) {
        $this->JRprop->setValue($value); //FIXME: handle php object values properly (node, binary, value object)
    }

    /**
     * overwritten to simplify.
     *
     * @return the node containing this property
     * @throws ItemNotFoundException Can not happen as property always has a parent
     * @throws AccessDeniedException If the current {@link Ticket} does not have sufficient access rights to complete the operation.
     * @throws RepositoryException If another error occurs.
     */
    public function getParent() {
        return $this->parentNode;
    }

    /**
     *
     * @return bool
     * Returns TRUE if this {@link Item} is a {@link Node};
     * Returns FALSE if this {@link Item} is a {@link Property}.
     * @see PHPCR_Item::isNode()
     */
    public function isNode() {
        return false;
    }

    /**
     * Returns a Binary representation of the value of this property. A
     * shortcut for Property.getValue().getBinary(). See Value.
     *
     * @return PHPCR_BinaryInterface A Binary representation of the value of this property.
     * @throws PHPCR_ValueFormatException if the property is multi-valued.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getBinary() {
        return new jr_cr_binary($this->JRprop->getBinary());
    }

    /**
     * Returns a BigDecimal representation of the value of this property. A
     * shortcut for Property.getValue().getDecimal(). See Value.
     *
     * @return float A float representation of the value of this property.
     * @throws PHPCR_ValueFormatException if conversion to a BigDecimal is not possible or if the property is multi-valued.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getDecimal() {
        return $this->JRprop->getDecimal()->doubleValue(); //php float and double are the same, but we prefer the better precision

    }

    /**
     * If this property is of type PATH (or convertible to this type) this
     * method returns the Property to which this property refers.
     * If this property contains a relative path, it is interpreted relative
     * to the parent node of this property. Therefore, when resolving such a
     * relative path, the segment "." refers to the parent node itself, ".." to
     * the parent of the parent node and "foo" to a sibling property of this
     * property or this property itself.
     *
     * For example, if this property is located at /a/b/c and it has a value of
     * "../d" then this method will return the property at /a/d if such exists.
     *
     * @return PHPCR_PropertyInterface the referenced property
     * @throws PHPCR_ValueFormatException if this property cannot be converted to a PATH, if the property is multi-valued or if this property is a referring type but is currently part of the frozen state of a version in version storage.
     * @throws PHPCR_ItemNotFoundException If no property accessible by the current Session exists in this workspace at the specified path. Note that this applies even if a node exists at the specified location. To dereference to a target node, the method Property.getNode is used.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getProperty() {
        //TODO: Insert Code
    }

    /**
     * Returns TRUE if this property is multi-valued and FALSE if this property
     * is single-valued.
     *
     * @return boolean TRUE if this property is multi-valued; FALSE otherwise.
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function isMultiple() {
        //TODO: Insert Code
    }
}
