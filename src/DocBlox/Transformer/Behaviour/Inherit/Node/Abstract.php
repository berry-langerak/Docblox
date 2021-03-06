<?php
/**
 * Provide a short description for this class.
 *
 * @package    emma
 * @subpackage
 * @author     mvriel
 */
abstract class DocBlox_Transformer_Behaviour_Inherit_Node_Abstract
{
    /** @var DOMElement */
    protected $node = null;

    /** @var string[] All class tags that are inherited when none are defined */
    protected $inherited_tags = array(
        'version',
        'copyright',
        'author'
    );

    /**
     * Initialize the inheritance for this node.
     *
     * @param DOMElement $node
     */
    public function __construct(DOMElement $node)
    {
        $this->node = $node;
    }

    /**
     * Returns the name of the given method or property node.
     *
     * @return string
     */
    protected function getNodeName()
    {
        return current($this->getDirectElementsByTagName($this->node, 'name'))
            ->nodeValue;
    }

    /**
     * Returns the docblock element for the given node; if none exists it will
     * be added.
     *
     * @return DOMElement
     */
    protected function getDocBlockElement()
    {
        $docblocks = $this->getDirectElementsByTagName($this->node, 'docblock');

        // if this method does not yet have a docblock; add one; even
        // though DocBlox throws a warning about a missing DocBlock!
        if (count($docblocks) < 1) {
            $docblock = new DOMElement('docblock');
            $this->node->appendChild($docblock);
        } else {
            /** @var DOMElement $docblock  */
            $docblock = reset($docblocks);
        }

        return $docblock;
    }

    /**
     * Returns the elements with the given tag name that can be found
     * as direct children of $node.
     *
     * getElementsByTagName returns all DOMElements with the given tag name
     * regardless where in the DOM subtree they are. This method checks whether
     * the parent node matches the given node and thus determines whether it is
     * a direct child.
     *
     * @param DOMElement $node
     * @param string     $element_name
     *
     * @return DOMElement[]
     */
    protected function getDirectElementsByTagName(DOMElement $node, $element_name)
    {
        $result   = array();
        $elements = $node->getElementsByTagName($element_name);
        for($i = 0; $i < $elements->length; $i++)
        {
            if ($elements->item($i)->parentNode !== $node)
            {
                continue;
            }

            $result[] = $elements->item($i);
        }

        return $result;
    }

    /**
     * Copies the short description from the Super element's DocBlock to the
     * Sub element's DocBlock if the sub element has none.
     *
     * @param DOMElement $super_docblock
     * @param DOMElement $docblock
     *
     * @return void
     */
    public function copyShortDescription(DOMElement $super_docblock,
        DOMElement $docblock)
    {
        /** @var DOMElement $desc  */
        $desc = current($this->getDirectElementsByTagName($docblock, 'description'));

        $super_desc = current(
            $this->getDirectElementsByTagName($super_docblock, 'description')
        );

        if ((($desc === false) || (!trim($desc->nodeValue)))
            && ($super_desc !== false)
        ) {
            if ($desc !== false) {
                $docblock->removeChild($desc);
            }

            $docblock->appendChild(clone $super_desc);
        }
    }

    /**
     * Copies the long description from the Super element's DocBlock to the
     * Sub element's DocBlock if the sub element has none.
     *
     * @param DOMElement $super_docblock
     * @param DOMElement $docblock
     *
     * @return void
     */
    public function copyLongDescription(DOMElement $super_docblock,
        DOMElement $docblock)
    {
        /** @var DOMElement $desc  */
        $desc = current(
            $this->getDirectElementsByTagName($docblock, 'long-description')
        );

        $super_desc = current(
            $this->getDirectElementsByTagName($super_docblock, 'long-description')
        );

        if ((($desc === false) || (!trim($desc->nodeValue)))
            && ($super_desc !== false)
        ) {
            if ($desc !== false) {
                $docblock->removeChild($desc);
            }

            $docblock->appendChild(clone $super_desc);
        }
    }

    /**
     * Copies the tags from the super docblock to this one if it matches
     * the criteria.
     *
     * Criteria for copying are:
     *
     * * Tag name must be in the list of to-be-copied tag names
     * * No tag with that name may be in the sub element
     *
     * @param string[]   $tag_types      array of to-be-copied tag names.
     * @param DOMElement $super_docblock DocBlock of the super element.
     * @param DOMElement $docblock       DocBlock of the sub element.
     *
     * @return void
     */
    protected function copyTags(array $tag_types, DOMElement $super_docblock,
        DOMElement $docblock)
    {
        // get the names of all existing tags because we should only add
        // parent tags if there are none in the existing docblock
        $existing_tag_names = array();
        foreach ($this->getDirectElementsByTagName($docblock, 'tag') as $tag)
        {
            $existing_tag_names[] = $tag->getAttribute('name');
        }
        $existing_tag_names = array_unique($existing_tag_names);

        /** @var DOMElement $tag */
        foreach ($this->getDirectElementsByTagName($super_docblock, 'tag') as $tag) {
            $tag_name = $tag->getAttribute('name');

            if (in_array($tag_name, $tag_types)
                && (!in_array($tag_name, $existing_tag_names))
            ) {
                $child = clone $tag;
                $child->setAttribute('line', $this->node->getAttribute('line'));
                $docblock->appendChild($child);
            }
        }
    }

    /**
     * Combines the docblock of an overridden method with this one if applicable.
     *
     * @param mixed[]    $super        Array containing a flat list of methods for
     *     a tree of inherited classes.
     * @param string     $class_name   Name of the current class.
     *
     * @return void
     */
    public function apply(array &$super, $class_name)
    {
        // the name is always the first encountered child element with
        // tag name 'name'
        $node_name = $this->getNodeName();

        // only process if the super has a node with this name
        if (isset($super[$node_name])) {
            $docblock = $this->getDocBlockElement();

            /** @var DOMElement $super_object  */
            $super_object = $super[$node_name]['object'];

            /** @var DOMElement $super_docblock  */
            $super_docblock = current($this->getDirectElementsByTagName(
                $super_object, 'docblock'
            ));
            $super_class    = current($this->getDirectElementsByTagName(
                $super_object->parentNode, 'full_name'
            ))->nodeValue;

            // add an element which defines which class' element you override
            $this->node->appendChild(new DOMElement('overrides-from', $super_class));

            $this->copyShortDescription($super_docblock, $docblock);
            $this->copyLongDescription($super_docblock, $docblock);
            $this->copyTags($this->inherited_tags, $super_docblock, $docblock);
        }

        // only add if this has a docblock; otherwise it is useless
        $docblocks = $this->getDirectElementsByTagName($this->node, 'docblock');
        if (count($docblocks) > 0) {
            $super[$node_name] = array(
                'class' => $class_name,
                'object' => $this->node
            );
        }
    }

}