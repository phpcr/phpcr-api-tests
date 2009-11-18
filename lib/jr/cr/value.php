<?php
class jr_cr_value implements PHPCR_ValueInterface {
    protected $JRvalue;
    protected $isStream = null;

    public function __construct($JRvalue) {
        $this->JRvalue = $JRvalue;
    }

    /**
     * Returns a string representation of this value.
     *
     * @return string
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a string is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getString() {
        try {
            //FIXME: what should we do if JRvalue is null (meaning a row->getValue did return null)
            //this might be a bug of jackrabbit 2.0 alpha5, on value for createdBy
            if ($this->JRvalue==null) return '';
            return $this->JRvalue->getString();
        } catch(JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
    }

    /**
     * Returns the integer representation of this value.
     *
     * @throws PHPCR_ValueFormatException If conversion to a int is not possible.
     * @throws PHPCR_RepositoryException If another error occurs.
     *
     * @return int
     */
    public function getLong() {
        return $this->getNumber();
    }

    /**
     * Returns the float/double representation of this value.
     *
     * This method should always return exactly what {@link getFloat()} does.
     * It has been left as a requirement to satisfy JCR compliance.
     *
     * @see getFloat()
     * @return float
     */
    public function getDouble() {
        return $this->getNumber(true);
    }

    /**
     * Returns a DateTime representation of this value.
     *
     * The object returned is a copy of the stored value, so changes to it are
     * not reflected in internal storage.
     *
     * @return DateTime A DateTime representation of the value of this property.
     * @throws PHPCR_ValueFormatException if conversion to a DateTime is not possible.
     * @throws PHPCR_RepositoryException if another error occurs.
     * @api
     */
    public function getDate() {
        try {
            $date = $this->JRvalue->getDate();
            $date = date_create($date->getTime()->toString());
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }

        if (! $date instanceOf DateTime) {
            throw new PHPCR_ValueFormatException('Could not get Date');
        }

        return $date;
    }

    /**
     * Returns the boolean representation of this value.
     *
     * @return bool
     *
     * @throws PHPCR_ValueFormatException If conversion to a boolean is not possible.
     * @throws PHPCR_RepositoryException If another error occurs.
     */
    public function getBoolean() {
        try {
            $bool = $this->JRvalue->getBoolean();
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
        return $bool;
    }

    /**
     * Returns the type of this Value.
     * One of:
     * <ul>
     *    <li>{@link PropertyType::STRING}</li>
     *    <li>{@link PropertyType::DATE}</li>
     *    <li>{@link PropertyType::BINARY}</li>
     *    <li>{@link PropertyType::DOUBLE}</li>
     *    <li>{@link PropertyType::LONG}</li>
     *    <li>{@link PropertyType::BOOLEAN}</li>
     *    <li>{@link PropertyType::NAME}</li>
     *    <li>{@link PropertyType::PATH}</li>
     *    <li>{@link PropertyType::REFERENCE}</li>
     * </ul>
     *
     * The type returned is that which was set at property creation.
     *
     * @see PropertyType
     * @return int
     */
    public function getType() {
        return $this->JRvalue->getType();
    }

    /**
     * Returns a Binary representation of this value. The Binary object in turn provides
     * methods to access the binary data itself. Uses the standard conversion to binary
     * (see JCR specification).
     *
     * @return PHPCR_BinaryInterface A Binary representation of this value.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getBinary() {
        return new jr_cr_binary($this->JRvalue->getBinary());
    }

    /**
     * Returns a BigDecimal representation of this value.
     *
     * @return float A double representation of the value of this property.
     * @throws PHPCR_ValueFormatException if conversion is not possible.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getDecimal() {
        return $this->getNumber(true);
    }

    /**
     * Returns a number of the value. Which format can be given as param.
     * Used internally for the various numerical get methods.
     * In PHP, there is no distinction between float and double.
     *
     * @throws PHPCR_ValueFormatException if conversion is not possible.
     * @throws PHPCR_RepositoryException if another error occurs.
    *
     * @param float boolean If true, will return float value, otherwise integer. defaults to false.
     * @return int or float depending on parameter
     */
    protected function getNumber($float = false) {
        try {
            if (true === $float) {
                $num = $this->JRvalue->getDouble();
            } else {
                $num = $this->JRvalue->getLong();
            }
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }

        if (true === $float) {
            return (float) $num;
        } else {
            return (int)  $num;
        }
    }

    protected function throwExceptionFromJava($e) {
        $str = split("\n", $e->getMessage(), 2);
        if (false !== strpos($str[0], 'ValueFormatException')) {
            throw new PHPCR_ValueFormatException($e->getMessage());
        } else {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
}
