<?php

namespace Symphony\Symphony\Managers;

use Symphony\Symphony;

/**
 * The `SectionManager` is responsible for managing all Sections in a Symphony
 * installation by exposing basic CRUD operations. Sections are stored in the
 * database in `tbl_sections`.
 */
class SectionManager extends Symphony\AbstractManager
{
    /**
     * An array of all the objects that the Manager is responsible for.
     *
     * @var array
     *            Defaults to an empty array
     */
    protected static $_pool = [];

    /**
     * Takes an associative array of Section settings and creates a new
     * entry in the `tbl_sections` table, returning the ID of the Section.
     * The ID of the section is generated using auto_increment and returned
     * as the Section ID.
     *
     * @param array $settings
     *                        An associative of settings for a section with the key being
     *                        a column name from `tbl_sections`
     *
     * @throws DatabaseException
     *
     * @return int
     *             The newly created Section's ID
     */
    public static function add(array $settings)
    {
        $defaults = [];
        $defaults['creation_date'] = $defaults['modification_date'] = Symphony\DateTimeObj::get('Y-m-d H:i:s');
        $defaults['creation_date_gmt'] = $defaults['modification_date_gmt'] = Symphony\DateTimeObj::getGMT('Y-m-d H:i:s');
        $defaults['author_id'] = 1;
        $defaults['modification_author_id'] = 1;
        $settings = array_replace($defaults, $settings);
        if (!\Symphony::Database()->insert($settings, 'tbl_sections')) {
            return false;
        }

        return \Symphony::Database()->getInsertID();
    }

    /**
     * Updates an existing Section given it's ID and an associative
     * array of settings. The array does not have to contain all the
     * settings for the Section as there is no deletion of settings
     * prior to updating the Section.
     *
     * @param int   $section_id
     *                          The ID of the Section to edit
     * @param array $settings
     *                          An associative of settings for a section with the key being
     *                          a column name from `tbl_sections`
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public static function edit($section_id, array $settings)
    {
        $defaults = [];
        $defaults['modification_date'] = Symphony\DateTimeObj::get('Y-m-d H:i:s');
        $defaults['modification_date_gmt'] = Symphony\DateTimeObj::getGMT('Y-m-d H:i:s');
        $defaults['author_id'] = 1;
        $defaults['modification_author_id'] = 1;
        $settings = array_replace($defaults, $settings);
        if (!\Symphony::Database()->update($settings, 'tbl_sections', sprintf(' `id` = %d', $section_id))) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a Section by Section ID, removing all entries, fields, the
     * Section and any Section Associations in that order.
     *
     * @param int $section_id
     *                        The ID of the Section to delete
     *
     * @throws DatabaseException
     * @throws Exception
     *
     * @return bool
     *              Returns true when completed
     */
    public static function delete($section_id)
    {
        $details = \Symphony::Database()->fetchRow(0, sprintf(
            '
            SELECT `sortorder` FROM tbl_sections WHERE `id` = %d',
            $section_id
        ));

        // Delete all the entries
        $entries = \Symphony::Database()->fetchCol('id', "SELECT `id` FROM `tbl_entries` WHERE `section_id` = '$section_id'");
        EntryManager::delete($entries);

        // Delete all the fields
        $fields = FieldManager::fetch(null, $section_id);

        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field) {
                FieldManager::delete($field->get('id'));
            }
        }

        // Delete the section
        \Symphony::Database()->delete('tbl_sections', sprintf(
            '
            `id` = %d',
            $section_id
        ));

        // Update the sort orders
        \Symphony::Database()->query(sprintf(
            '
            UPDATE tbl_sections
            SET `sortorder` = (`sortorder` - 1)
            WHERE `sortorder` > %d',
            $details['sortorder']
        ));

        // Delete the section associations
        \Symphony::Database()->delete('tbl_sections_association', sprintf(
            '
            `parent_section_id` = %d',
            $section_id
        ));

        return true;
    }

    /**
     * Returns a Section object by ID, or returns an array of Sections
     * if the Section ID was omitted. If the Section ID is omitted, it is
     * possible to sort the Sections by providing a sort order and sort
     * field. By default, Sections will be order in ascending order by
     * their name.
     *
     * @param int|array $section_id
     *                              The ID of the section to return, or an array of ID's. Defaults to null
     * @param string    $order
     *                              If `$section_id` is omitted, this is the sortorder of the returned
     *                              objects. Defaults to ASC, other options id DESC
     * @param string    $sortfield
     *                              The name of the column in the `tbl_sections` table to sort
     *                              on. Defaults to name
     *
     * @throws DatabaseException
     *
     * @return Section|array
     *                       A Section object or an array of Section objects
     */
    public static function fetch($section_id = null, $order = 'ASC', $sortfield = 'name')
    {
        $returnSingle = false;
        $section_ids = [];

        if (null !== $section_id) {
            if (!is_array($section_id)) {
                $returnSingle = true;
                $section_ids = array($section_id);
            } else {
                $section_ids = $section_id;
            }
        }

        if ($returnSingle && isset(self::$_pool[$section_id])) {
            return self::$_pool[$section_id];
        }

        // Ensure they are always an ID
        $section_ids = array_map('intval', $section_ids);
        $sql = sprintf(
            'SELECT `s`.*
            FROM `tbl_sections` AS `s`
            %s
            %s',
            !empty($section_id) ? ' WHERE `s`.`id` IN ('.implode(',', $section_ids).') ' : '',
            empty($section_id) ? " ORDER BY `s`.`$sortfield` $order" : ''
        );

        if (!$sections = \Symphony::Database()->fetch($sql)) {
            return $returnSingle ? false : array();
        }

        $ret = [];

        foreach ($sections as $s) {
            $obj = self::create();

            foreach ($s as $name => $value) {
                $obj->set($name, $value);
            }

            $obj->set('creation_date', Symphony\DateTimeObj::get('c', $obj->get('creation_date')));

            $modDate = $obj->get('modification_date');
            if (!empty($modDate)) {
                $obj->set('modification_date', Symphony\DateTimeObj::get('c', $obj->get('modification_date')));
            } else {
                $obj->set('modification_date', $obj->get('creation_date'));
            }

            self::$_pool[$obj->get('id')] = $obj;

            $ret[] = $obj;
        }

        return 1 == count($ret) && $returnSingle ? $ret[0] : $ret;
    }

    /**
     * Return a Section ID by the handle.
     *
     * @param string $handle
     *                       The handle of the section
     *
     * @return int
     *             The Section ID
     */
    public static function fetchIDFromHandle($handle)
    {
        $handle = \Symphony::Database()->cleanValue($handle);

        return \Symphony::Database()->fetchVar('id', 0, "SELECT `id` FROM `tbl_sections` WHERE `handle` = '$handle' LIMIT 1");
    }

    /**
     * Work out the next available sort order for a new section.
     *
     * @return int
     *             Returns the next sort order
     */
    public static function fetchNextSortOrder()
    {
        $next = \Symphony::Database()->fetchVar(
            'next',
            0,
            'SELECT
                MAX(p.sortorder) + 1 AS `next`
            FROM
                `tbl_sections` AS p
            LIMIT 1'
        );

        return $next ? (int) $next : 1;
    }

    /**
     * Returns a new Section object, using the SectionManager
     * as the Section's $parent.
     *
     * @return Section
     */
    public static function create()
    {
        return new Symphony\Section();
    }

    /**
     * Create an association between a section and a field.
     *
     * @since Symphony 2.3
     *
     * @param int  $parent_section_id
     *                                The linked section id
     * @param int  $child_field_id
     *                                The field ID of the field that is creating the association
     * @param int  $parent_field_id   (optional)
     *                                The field ID of the linked field in the linked section
     * @param bool $show_association  (optional)
     *                                Whether of not the link should be shown on the entries table of the
     *                                linked section. This defaults to true.
     *
     * @throws DatabaseException
     * @throws Exception
     *
     * @return bool
     *              true if the association was successfully made, false otherwise
     */
    public static function createSectionAssociation($parent_section_id = null, $child_field_id = null, $parent_field_id = null, $show_association = true, $interface = null, $editor = null)
    {
        if (null === $parent_section_id && (null === $parent_field_id || !$parent_field_id)) {
            return false;
        }

        if (null === $parent_section_id) {
            $parent_field = FieldManager::fetch($parent_field_id);
            $parent_section_id = $parent_field->get('parent_section');
        }

        $child_field = FieldManager::fetch($child_field_id);
        $child_section_id = $child_field->get('parent_section');

        $fields = array(
            'parent_section_id' => $parent_section_id,
            'parent_section_field_id' => $parent_field_id,
            'child_section_id' => $child_section_id,
            'child_section_field_id' => $child_field_id,
            'hide_association' => ($show_association ? 'no' : 'yes'),
            'interface' => $interface,
            'editor' => $editor,
        );

        return \Symphony::Database()->insert($fields, 'tbl_sections_association');
    }

    /**
     * Permanently remove a section association for this field in the database.
     *
     * @since Symphony 2.3
     *
     * @param int $field_id
     *                      the field ID of the linked section's linked field
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public static function removeSectionAssociation($field_id)
    {
        return \Symphony::Database()->delete('tbl_sections_association', sprintf(
            '`child_section_field_id` = %1$d OR `parent_section_field_id` = %1$d',
            $field_id
        ));
    }

    /**
     * Returns the association settings for the given field id. This is to be used
     * when configuring the field so we can correctly show the association setting
     * the UI.
     *
     * @since Symphony 2.6.0
     *
     * @param int $field_id
     *
     * @return string
     */
    public static function getSectionAssociationSetting($field_id)
    {
        // We must inverse the setting. The database stores 'hide', whereas the UI
        // refers to 'show'. Hence if the database says 'yes', it really means, hide
        // the association. In the UI, this needs to be flipped to 'no' so the checkbox
        // won't be checked.
        return \Symphony::Database()->fetchVar('show_association', 0, sprintf('
            SELECT
            CASE hide_association WHEN "no" THEN "yes" ELSE "no" END as show_association
            FROM `tbl_sections_association`
            WHERE `child_section_field_id` = %d
        ', $field_id));
    }

    /**
     * Returns any section associations this section has with other sections
     * linked using fields, and where this section is the parent in the association.
     * Has an optional parameter, `$respect_visibility` that
     * will only return associations that are deemed visible by a field that
     * created the association. eg. An articles section may link to the authors
     * section, but the field that links these sections has hidden this association
     * so an Articles column will not appear on the Author's Publish Index.
     *
     * @since Symphony 2.3.3
     *
     * @param int  $section_id
     *                                 The ID of the section
     * @param bool $respect_visibility
     *                                 Whether to return all the section associations regardless of if they
     *                                 are deemed visible or not. Defaults to false, which will return all
     *                                 associations.
     *
     * @throws DatabaseException
     *
     * @return array
     */
    public static function fetchChildAssociations($section_id, $respect_visibility = false)
    {
        return \Symphony::Database()->fetch(sprintf(
            '
            SELECT *
            FROM `tbl_sections_association` AS `sa`, `tbl_sections` AS `s`
            WHERE `sa`.`parent_section_id` = %d
            AND `s`.`id` = `sa`.`child_section_id`
            %s
            ORDER BY `s`.`sortorder` ASC',
            $section_id,
            ($respect_visibility) ? "AND `sa`.`hide_association` = 'no'" : ''
        ));
    }

    /**
     * Returns any section associations this section has with other sections
     * linked using fields, and where this section is the child in the association.
     * Has an optional parameter, `$respect_visibility` that
     * will only return associations that are deemed visible by a field that
     * created the association. eg. An articles section may link to the authors
     * section, but the field that links these sections has hidden this association
     * so an Articles column will not appear on the Author's Publish Index.
     *
     * @since Symphony 2.3.3
     *
     * @param int  $section_id
     *                                 The ID of the section
     * @param bool $respect_visibility
     *                                 Whether to return all the section associations regardless of if they
     *                                 are deemed visible or not. Defaults to false, which will return all
     *                                 associations.
     *
     * @throws DatabaseException
     *
     * @return array
     */
    public static function fetchParentAssociations($section_id, $respect_visibility = false)
    {
        return \Symphony::Database()->fetch(sprintf(
            'SELECT *
            FROM `tbl_sections_association` AS `sa`, `tbl_sections` AS `s`
            WHERE `sa`.`child_section_id` = %d
            AND `s`.`id` = `sa`.`parent_section_id`
            %s
            ORDER BY `s`.`sortorder` ASC',
            $section_id,
            ($respect_visibility) ? "AND `sa`.`hide_association` = 'no'" : ''
        ));
    }
}
