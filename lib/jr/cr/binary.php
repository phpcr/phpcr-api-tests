<?php
class jr_cr_binary implements PHPCR_BinaryInterface {
    protected $JRbinary;
    protected $isStream = null;

    public function __construct($JRbinary) {
        $this->JRbinary = $JRbinary;
    }


    /**
    * Returns a stream representation of this value.
    * Each call to getStream() returns a new stream.
    * The API consumer is responsible for calling close() on the returned
    * stream.
    *
    * @return resource A stream representation of this value.
    * @throws BadMethodCallException if dispose() has already been called on this Binary
    * @throws PHPCR_RepositoryException if an error occurs.
    * @api
    */
    public function getStream() {
        //TODO: Code here
    }

    /**
    * Reads successive bytes from the specified position in this Binary into
    * the passed string until the end of the Binary is encountered.
    *
    * @param string $bytes the buffer into which the data is read.
    * @param integer $position the position in this Binary from which to start reading bytes.
    * @return integer the number of bytes read into the buffer
    * @throws RuntimeException if an I/O error occurs.
    * @throws InvalidArgumentException if offset is negative.
    * @throws BadMethodCallException if dispose() has already been called on this Binary
    * @throws PHPCR_RepositoryException if another error occurs.
    * @api
    */
    public function read(&$bytes, $position) {
        $byte_arr = array();
        $c = $this->JRbinary->read($byte_arr, $position);
        $bytes = pack('C', $byte_arr);
        return $c;
    }

    /**
    * Returns the size of this Binary value in bytes.
    *
    * @return integer the size of this value in bytes.
    * @throws BadMethodCallException if dispose() has already been called on this Binary
    * @throws PHPCR_RepositoryException if an error occurs.
    * @api
    */
    public function getSize() {
        return $this->JRbinary->getSize();
    }

    /**
    * Releases all resources associated with this Binary object and informs the
    * repository that these resources may now be reclaimed.
    * An application should call this method when it is finished with the
    * Binary object.
    *
    * @return void
    * @api
    */
    public function dispose() {
        $this->JRbinary->dispose();
    }

}
