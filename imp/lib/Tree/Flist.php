<?php
/**
 * Copyright 2010-2016 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @category  Horde
 * @copyright 2010-2016 Horde LLC
 * @license   http://www.horde.org/licenses/gpl GPL
 * @package   IMP
 */

/**
 * The IMP_Tree_Flist class provides an IMP dropdown mailbox (folder tree)
 * list.
 *
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2010-2016 Horde LLC
 * @license   http://www.horde.org/licenses/gpl GPL
 * @package   IMP
 */
class IMP_Tree_Flist extends Horde_Tree_Renderer_Select
{
    /**
     * Filter list.
     *
     * @var array
     */
    protected $_filter = array();

    /**
     * Constructor.
     *
     * @param Horde_Tree $tree  A tree object.
     * @param array $params     Additional parameters.
     *   - abbrev: (integer) Abbreviate long mailbox names by replacing the
     *             middle of the name with '...'? Value is the total length
     *             of the string.
     *             DEFAULT: 30
     *   - container_select: (boolean) Allow containers to be selected?
     *                       DEFAULT: false
     *   - customhtml: (string) Custom HTML to add to the beginning of the HTML
     *                 SELECT tag.
     *                 DEFAULT: ''
     *   - filter: (array) An array of mailboxes to ignore.
     *             DEFAULT: Display all
     *   - heading: (string) The label for an empty-value option at the top of
     *              the list.
     *              DEFAULT: ''
     *   - inc_notepads: (boolean) Include user's editable notepads in list?
     *                   DEFAULT: No
     *   - inc_tasklists: (boolean) Include user's editable tasklists in list?
     *                    DEFAULT: No
     *   - inc_vfolder: (boolean) Include user's virtual folders in list?
     *                  DEFAULT: No
     *   - new_mbox: (boolean) Display an option to create a new mailbox?
     *               DEFAULT: No
     */
    public function __construct(Horde_Tree $tree, array $params = array())
    {
        $params = array_merge(array(
            'abbrev' => 30
        ), $params);

        parent::__construct($tree, $params);
    }

    /**
     * @param boolean $static  Ignored in this driver.
     */
    public function getTree($static = false)
    {
        global $injector;

        $this->_nodes = $this->_tree->getNodes();

        $filter = $injector->createInstance('Horde_Text_Filter');

        $view = new Horde_View(array(
            'templatePath' => IMP_TEMPLATES . '/flist'
        ));
        $view->addHelper('FormTag');
        $view->addHelper('Tag');

        $view->optgroup = $this->getOption('optgroup');

        /* Custom HTML. */
        if ($customhtml = $this->getOption('customhtml')) {
            $view->customhtml = $customhtml;
        }

        /* Heading. */
        if (($heading = $this->getOption('heading')) &&
            (strlen($heading) > 0)) {
            $view->heading = $heading;
        }

        /* New mailbox entry. */
        if ($this->getOption('new_mbox')) {
            $imp_imap = $injector->getInstance('IMP_Factory_Imap')->create();
            if ($imp_imap->access(IMP_Imap::ACCESS_CREATEMBOX) &&
                $imp_imap->access(IMP_Imap::ACCESS_CREATEMBOX_MAX)) {
                $view->new_mbox = true;
            }
        }

        /* Virtual folders. */
        if ($this->getOption('inc_vfolder')) {
            $iterator = IMP_Search_IteratorFilter::create(
                IMP_Search_IteratorFilter::VFOLDER
            );
            $vfolder_list = array();

            foreach ($iterator as $val) {
                $form_to = IMP_Mailbox::formTo($val);
                $vfolder_list[] = array(
                    'l' => $filter->filter($val->label, 'space2html', array('encode' => true)),
                    'sel' => !empty($this->_nodes[$form_to]['selected']),
                    'v' => $form_to
                );
            }

            if (!empty($vfolder_list)) {
                $view->vfolder = $vfolder_list;
            }
        }

        /* Add the list of editable tasklists to the list. */
        if ($this->getOption('inc_tasklists')) {
            $tasklist = new IMP_Indices_Copy_Tasklist();
            $view->tasklist = array();

            foreach ($tasklist->getTasklists() as $key => $val) {
                $view->tasklist[] = array(
                    'l' => $filter->filter($val->get('name'), 'space2html', array('encode' => true)),
                    'v' => $key
                );
            }
        }

        /* Add the list of editable notepads to the list. */
        if ($this->getOption('inc_notepads')) {
            $notepad = new IMP_Indices_Copy_Notepad();
            $view->notepad = array();

            foreach ($notepad->getNotepads() as $key => $val) {
                $view->notepad[] = array(
                    'l' => $filter->filter($val->get('name'), 'space2html', array('encode' => true)),
                    'v' => $key
                );
            }
        }

        /* Prepare filter list. */
        $this->_filter = ($filter = $this->getOption('filter'))
            ? array_flip($filter)
            : array();

        $tree = '';
        foreach ($this->_tree->getRootNodes() as $node_id) {
            $tree .= $this->_buildTree($node_id);
        }
        $view->tree = $tree;

        return $view->render('flist');
    }

    /**
     */
    protected function _buildTree($node_id)
    {
        if (isset($this->_filter[$node_id])) {
            return '';
        }

        $node = &$this->_nodes[$node_id];

        if ($abbrev = $this->getOption('abbrev')) {
            $orig_label = $node['label'];
            $node['label'] = Horde_String::abbreviate($node['orig_label'], $abbrev - ($node['indent'] * 2));
        } else {
            $orig_label = null;
        }

        /* Ignore container elements. */
        if (!$this->getOption('container_select') &&
            !empty($node['container'])) {
            if (!empty($node['vfolder'])) {
                return '';
            }
            $node_id = '';
            $this->_nodes[$node_id] = $node;
        }

        $out = parent::_buildTree($node_id);

        if ($orig_label) {
            $node['label'] = $orig_label;
        }

        return $out;
    }

}
