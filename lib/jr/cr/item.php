<?php
/** base class for common methods in property and node */
abstract class jr_cr_item implements PHPCR_ItemInterface {
    protected $new = false;
    protected $modified = false;
    protected $name = '';

    public $JRitem = null;
    /**
     * @var jr_cr_session
     */
    protected $session = null;

    function __construct($session, $JRitem) {
        if (! $JRitem) die('ha!');
        $this->session = $session;
        $this->JRitem = $JRitem;
    }

    /**
     * @param object
     * A {@link ItemVisitor} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::accept()
     */
    public function accept(PHPCR_ItemVisitorInterface $visitor) {
        $visitor->visit($this);
    }

    /**
     *
     * @param int
     * An integer, 0 &lt;= $degree &lt;= n where
     * n is the depth of $this {@link Item} along the
     * path returned by {@link getPath()}.
     * @return object
     * The ancestor of the specified absolute degree of $this
     * {@link Item} along the path returned by{@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If $degree &lt; 0 or $degree &gt; n
     * where n is the is the depth of $this {@link Item}
     * along the path returned by {@link getPath()}.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getAncestor()
     */
    public function getAncestor($degree) {
        if ($degree == $this->getDepth()) return $this;
        //everything above $this must be node, properties do not have children.
        return new jr_cr_node($this->session, $this->JRitem->getAncestor($degree));
//TODO error handling
    }

    /**
     *
     * @return int
     * The depth of this {@link Item} in the repository hierarchy.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getDepth()
     */
    public function getDepth() {
        return $this->JRitem->getDepth();
    }

    /**
     *
     * @return string
     * The (or a) name of this {@link Item} or an empty string if this
     * {@link Item} is the root {@link Node}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getName()
     */
    public function getName() {
        if (!$this->name) {
            $this->name = $this->JRitem->getName();
        }
        return $this->name;
    }

    /**
     *
     * @return PHPCR_NodeInterface parent of this Item along the path returned by
     * {@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If there is no parent.  This only happens if $this
     * {@link Item} is the root node.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getParent()
     */
    public function getParent() {
        try {
        $p = $this->JRitem->getParent();
        return new jr_cr_node($this->session,$p);
        } catch (Exception $e) {
            throw new PHPCR_ItemNotFoundException;
        }
    }

    /**
     *
     * @return string
     * The path (or one of the paths) of this {@link Item}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getPath()
     */
    public function getPath() {
        if (!$this->path) {
            $this->path = $this->JRitem->getPath();
        }
        return $this->path;
    }

    /**
     *
     * @return object
     * A {@link Session} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getSession()
     */
    public function getSession() {
        return $this->session;
    }

    /**
     *
     * @return boolean
     * @see PHPCR_Item::isModified()
     */
    public function isModified() {
        return $this->modified;
    }

    public function setModified($m) {
        if ($m) {
            $this->session->addNodeToModifiedList($this);
        }
        $this->modified = $m;
    }

    /**
     *
     * @return boolean
     * @see PHPCR_Item::isNew()
     */
    public function isNew() {
        return $this->new;
    }

    public function setNew($new) {
        $this->new = $new;
        if ($new) {
            $this->session->addNodeToList($this);
            $this->session->addNodeToModifiedList($this);
        }
    }

    /**
     *
     * @param object
     * A {@link Item} object
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::isSame()
     */
    public function isSame(PHPCR_ItemInterface $otherItem) {
        return $this->JRitem->isSame($otherItem->JRitem);
    }

    /**
     *
     * @param boolean
     * @throws {@link InvalidItemStateException}
     * If this {@link Item} object represents a workspace item that has been
     * removed (either by this session or another).
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::refresh()
     */
    public function refresh($keepChanges) {
        $this->JRitem->refresh(); //TODO Error handling
    }

    /**
     *
     * @throws {@link VersionException}
     * If the parent node of this item is versionable and checked-in or is
     * non-versionable but its nearest versionable ancestor is checked-in
     * and this implementation performs this validation immediately instead
     * of waiting until {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the removal of this item and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If removing the specified item would violate a node type or
     * implementation-specific constraint and this implementation performs
     * this validation immediately instead of waiting until {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::remove()
     */
    public function remove() {
        $this->JRitem->remove(); //TODO Error handling
    }


    /**
     *
     * @throws {@link AccessDeniedException}
     * If any of the changes to be persisted would violate the access
     * privileges of the this {@link Session}. Also thrown if any of the
     * changes to be persisted would cause the removal of a node that is
     * currently referenced by a <i>REFERENCE</i> property that this
     * Session <i>does not</i> have read access to.
     * @throws {@link ItemExistsException}
     * If any of the changes to be persisted would be prevented by the
     * presence of an already existing     item in the workspace.
     * @throws {@link ConstraintViolationException}
     * If any of the changes to be persisted would violate a node type or
     * restriction. Additionally, a repository may use this exception to
     * enforce implementation- or configuration-dependent restrictions.
     * @throws {@link InvalidItemStateException}
     * If any of the changes to be persisted conflicts with a change already
     * persisted through another session and the implementation is such that
     * this conflict can only be detected at save-time and therefore was not
     * detected earlier, at change-time.
     * @throws {@link ReferentialIntegrityException}
     * If any of the changes to be persisted would cause the removal of a
     * node that is currently referenced by a <i>REFERENCE</i> property
     * that this {@link Session} has read access to.
     * @throws {@link VersionException}
     * If the {@link save()} would make a result in a change to persistent
     * storage that would violate the read-only status of a checked-in node.
     * @throws {@link LockException}
     * If the {@link save()} would result in a change to persistent storage
     * that would violate a lock.
     * @throws {@link NoSuchNodeTypeException}
     * If the {@link save()} would result in the addition of a node with an
     * unrecognized node type.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::save()
     */
    public function save() {
        $this->JRitem->save();
        $this->setModified(false);
        $this->setNew(false);
    }
}