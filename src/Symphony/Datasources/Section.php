<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Datasources;

use Symphony\Symphony;

/**
 * The `SectionDatasource` allows a user to retrieve entries from a given
 * section on the Frontend. This datasource type exposes the filtering provided
 * by the Fields in the given section to narrow the result set down. The resulting
 * entries can be grouped, sorted and allows pagination. Results can be chained
 * from other `SectionDatasource`'s using output parameters.
 *
 * @since Symphony 2.3
 * @see http://getsymphony.com/learn/concepts/view/data-sources/
 */
abstract class Section extends Symphony\AbstractDatasource
{
    /**
     * An array of Field objects that this Datasource has created to display
     * the results.
     */
    protected static $_fieldPool = [];

    /**
     * An array of the Symphony meta data parameters.
     */
    private static $_system_parameters = [
        'system:id',
        'system:author',
        'system:creation-date',
        'system:modification-date'
    ];

    /**
     * Set's the Section ID that this Datasource will use as it's source.
     *
     * @param int $source
     */
    public function setSource($source)
    {
        $this->_source = (int) $source;
    }

    /**
     * Return's the Section ID that this datasource is using as it's source.
     *
     * @return int
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * If this Datasource requires System Parameters to be output, this function
     * will return true, otherwise false.
     *
     * @return bool
     */
    public function canProcessSystemParameters()
    {
        if (!is_array($this->dsParamPARAMOUTPUT)) {
            return false;
        }

        foreach (self::$_system_parameters as $system_parameter) {
            if (true === in_array($system_parameter, $this->dsParamPARAMOUTPUT)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Given a name for the group, and an associative array that
     * contains three keys, `attr`, `records` and `groups`. Grouping
     * of Entries is done by the grouping Field at a PHP level, not
     * through the Database.
     *
     * @param string $element
     *                        The name for the XML node for this group
     * @param array  $group
     *                        An associative array of the group data, includes `attr`, `records`
     *                        and `groups` keys
     *
     * @throws Exception
     *
     * @return \XMLElement
     */
    public function processRecordGroup($element, array $group)
    {
        $xGroup = new \XMLElement($element, null, $group['attr']);

        if (is_array($group['records']) && !empty($group['records'])) {
            if (isset($group['records'][0])) {
                $data = $group['records'][0]->getData();
                $pool = \FieldManager::fetch(array_keys($data));
                self::$_fieldPool += $pool;
            }

            foreach ($group['records'] as $entry) {
                $xEntry = $this->processEntry($entry);

                if ($xEntry instanceof \XMLElement) {
                    $xGroup->appendChild($xEntry);
                }
            }
        }

        if (is_array($group['groups']) && !empty($group['groups'])) {
            foreach ($group['groups'] as $element => $group) {
                foreach ($group as $g) {
                    $xGroup->appendChild(
                        $this->processRecordGroup($element, $g)
                    );
                }
            }
        }

        if (!$this->_param_output_only) {
            return $xGroup;
        }
    }

    /**
     * Given an Entry object, this function will generate an XML representation
     * of the Entry to be returned. It will also add any parameters selected
     * by this datasource to the parameter pool.
     *
     * @param Entry $entry
     *
     * @throws Exception
     *
     * @return \XMLElement|bool
     *                         Returns boolean when only parameters are to be returned
     */
    public function processEntry(Entry $entry)
    {
        $data = $entry->getData();

        $xEntry = new \XMLElement('entry');
        $xEntry->setAttribute('id', $entry->get('id'));

        if (!empty($this->_associated_sections)) {
            $this->setAssociatedEntryCounts($xEntry, $entry);
        }

        if ($this->_can_process_system_parameters) {
            $this->processSystemParameters($entry);
        }

        foreach ($data as $field_id => $values) {
            if (!isset(self::$_fieldPool[$field_id]) || !is_object(self::$_fieldPool[$field_id])) {
                self::$_fieldPool[$field_id] = \FieldManager::fetch($field_id);
            }

            $this->processOutputParameters($entry, $field_id, $values);

            if (!$this->_param_output_only) {
                foreach ($this->dsParamINCLUDEDELEMENTS as $handle) {
                    list($handle, $mode) = preg_split('/\s*:\s*/', $handle, 2);

                    if (self::$_fieldPool[$field_id]->get('element_name') == $handle) {
                        self::$_fieldPool[$field_id]->appendFormattedElement($xEntry, $values, ('yes' === $this->dsParamHTMLENCODE ? true : false), $mode, $entry->get('id'));
                    }
                }
            }
        }

        if ($this->_param_output_only) {
            return true;
        }

        // This is deprecated and will be removed in Symphony 3.0.0
        if (in_array('system:date', $this->dsParamINCLUDEDELEMENTS)) {
            if (\Symphony::Log()) {
                \Symphony::Log()->pushDeprecateWarningToLog('system:date', 'system:creation-date` or `system:modification-date', array(
                    'message-format' => __('The `%s` data source field is deprecated.'),
                ));
            }
            $xDate = new \XMLElement('system-date');
            $xDate->appendChild(
                \General::createXMLDateObject(
                    DateTimeObj::get('U', $entry->get('creation_date')),
                    'created'
                )
            );
            $xDate->appendChild(
                \General::createXMLDateObject(
                    DateTimeObj::get('U', $entry->get('modification_date')),
                    'modified'
                )
            );
            $xEntry->appendChild($xDate);
        }

        return $xEntry;
    }

    /**
     * An entry may be associated to other entries from various fields through
     * the section associations. This function will set the number of related
     * entries as attributes to the main `<entry>` element grouped by the
     * related entry's section.
     *
     * @param \XMLElement $xEntry
     *                           The <entry> \XMLElement that the associated section counts will
     *                           be set on
     * @param Entry      $entry
     *                           The current entry object
     *
     * @throws Exception
     */
    public function setAssociatedEntryCounts(XMLElement &$xEntry, Entry $entry)
    {
        $associated_entry_counts = $entry->fetchAllAssociatedEntryCounts($this->_associated_sections);

        if (!empty($associated_entry_counts)) {
            foreach ($associated_entry_counts as $section_id => $fields) {
                foreach ($this->_associated_sections as $section) {
                    if ($section['id'] != $section_id) {
                        continue;
                    }

                    // For each related field show the count (#2083)
                    foreach ($fields as $field_id => $count) {
                        $field_handle = \FieldManager::fetchHandleFromID($field_id);
                        $section_handle = $section['handle'];
                        // Make sure attribute does not begin with a digit
                        if (preg_match('/^[0-9]/', $section_handle)) {
                            $section_handle = 'x-'.$section_handle;
                        }
                        if ($field_handle) {
                            $xEntry->setAttribute($section_handle.'-'.$field_handle, (string) $count);
                        }

                        // Backwards compatibility (without field handle)
                        $xEntry->setAttribute($section_handle, (string) $count);
                    }
                }
            }
        }
    }

    /**
     * Given an Entry object, this function will iterate over the `dsParamPARAMOUTPUT`
     * setting to see any of the Symphony system parameters need to be set.
     * The current system parameters supported are `system:id`, `system:author`,
     * `system:creation-date` and `system:modification-date`.
     * If these parameters are found, the result is added
     * to the `$param_pool` array using the key, `ds-datasource-handle.parameter-name`
     * For the moment, this function also supports the pre Symphony 2.3 syntax,
     * `ds-datasource-handle` which did not support multiple parameters.
     *
     * @param entry $entry
     *                     The Entry object that contains the values that may need to be added
     *                     into the parameter pool
     */
    public function processSystemParameters(Entry $entry)
    {
        if (!isset($this->dsParamPARAMOUTPUT)) {
            return;
        }

        // Support the legacy parameter `ds-datasource-handle`
        $key = 'ds-'.$this->dsParamROOTELEMENT;
        $singleParam = 1 == count($this->dsParamPARAMOUTPUT);

        foreach ($this->dsParamPARAMOUTPUT as $param) {
            // The new style of paramater is `ds-datasource-handle.field-handle`
            $param_key = $key.'.'.str_replace(':', '-', $param);

            if ('system:id' === $param) {
                $this->_param_pool[$param_key][] = $entry->get('id');

                if ($singleParam) {
                    $this->_param_pool[$key][] = $entry->get('id');
                }
            } elseif ('system:author' === $param) {
                $this->_param_pool[$param_key][] = $entry->get('author_id');

                if ($singleParam) {
                    $this->_param_pool[$key][] = $entry->get('author_id');
                }
            } elseif ('system:creation-date' === $param || 'system:date' === $param) {
                if ('system:date' === $param && \Symphony::Log()) {
                    \Symphony::Log()->pushDeprecateWarningToLog('system:date', 'system:creation-date', array(
                        'message-format' => __('The `%s` data source output parameter is deprecated.'),
                    ));
                }
                $this->_param_pool[$param_key][] = $entry->get('creation_date');

                if ($singleParam) {
                    $this->_param_pool[$key][] = $entry->get('creation_date');
                }
            } elseif ('system:modification-date' === $param) {
                $this->_param_pool[$param_key][] = $entry->get('modification_date');

                if ($singleParam) {
                    $this->_param_pool[$key][] = $entry->get('modification_date');
                }
            }
        }
    }

    /**
     * Given an Entry object, a `$field_id` and an array of `$data`, this
     * function iterates over the `dsParamPARAMOUTPUT` and will call the
     * field's (identified by `$field_id`) `getParameterPoolValue` function
     * to add parameters to the `$this->_param_pool`.
     *
     * @param Entry $entry
     * @param int   $field_id
     * @param array $data
     */
    public function processOutputParameters(Entry $entry, $field_id, array $data)
    {
        if (!isset($this->dsParamPARAMOUTPUT)) {
            return;
        }

        // Support the legacy parameter `ds-datasource-handle`
        $key = 'ds-'.$this->dsParamROOTELEMENT;
        $singleParam = 1 == count($this->dsParamPARAMOUTPUT);

        if ($singleParam && (!isset($this->_param_pool[$key]) || !is_array($this->_param_pool[$key]))) {
            $this->_param_pool[$key] = [];
        }

        foreach ($this->dsParamPARAMOUTPUT as $param) {
            if (self::$_fieldPool[$field_id]->get('element_name') !== $param) {
                continue;
            }

            // The new style of paramater is `ds-datasource-handle.field-handle`
            $param_key = $key.'.'.str_replace(':', '-', $param);

            if (!isset($this->_param_pool[$param_key]) || !is_array($this->_param_pool[$param_key])) {
                $this->_param_pool[$param_key] = [];
            }

            $param_pool_values = self::$_fieldPool[$field_id]->getParameterPoolValue($data, $entry->get('id'));

            if (is_array($param_pool_values)) {
                $this->_param_pool[$param_key] = array_merge($param_pool_values, $this->_param_pool[$param_key]);

                if ($singleParam) {
                    $this->_param_pool[$key] = array_merge($param_pool_values, $this->_param_pool[$key]);
                }
            } elseif (null !== $param_pool_values) {
                $this->_param_pool[$param_key][] = $param_pool_values;

                if ($singleParam) {
                    $this->_param_pool[$key][] = $param_pool_values;
                }
            }
        }
    }

    /**
     * This function iterates over `dsParamFILTERS` and builds the relevant
     * `$where` and `$joins` parameters with SQL. This SQL is generated from
     * `Field->buildDSRetrievalSQL`. A third parameter, `$group` is populated
     * with boolean from `Field->requiresSQLGrouping()`.
     *
     * @param string $where
     * @param string $joins
     * @param bool   $group
     *
     * @throws Exception
     */
    public function processFilters(&$where, &$joins, &$group)
    {
        if (!is_array($this->dsParamFILTERS) || empty($this->dsParamFILTERS)) {
            return;
        }

        $pool = \FieldManager::fetch(array_filter(array_keys($this->dsParamFILTERS), 'is_int'));
        self::$_fieldPool += $pool;

        if (!is_string($where)) {
            $where = '';
        }

        foreach ($this->dsParamFILTERS as $field_id => $filter) {
            if ((is_array($filter) && empty($filter)) || '' == trim($filter)) {
                continue;
            }

            if (!is_array($filter)) {
                $filter_type = self::determineFilterType($filter);
                $value = self::splitFilter($filter_type, $filter);
            } else {
                $filter_type = self::FILTER_OR;
                $value = $filter;
            }

            if (!in_array($field_id, self::$_system_parameters) && 'id' != $field_id && !(self::$_fieldPool[$field_id] instanceof Field)) {
                throw new Exception(
                    __(
                        'Error creating field object with id %1$d, for filtering in data source %2$s. Check this field exists.',
                        array($field_id, '<code>'.$this->dsParamROOTELEMENT.'</code>')
                    )
                );
            }

            // Support system:id as well as the old 'id'. #1691
            if ('system:id' === $field_id || 'id' === $field_id) {
                if (self::FILTER_AND == $filter_type) {
                    $value = array_map(function ($val) {
                        return explode(',', $val);
                    }, $value);
                } else {
                    $value = array($value);
                }

                foreach ($value as $v) {
                    $c = 'IN';
                    if (0 === stripos($v[0], 'not:')) {
                        $v[0] = preg_replace('/^not:\s*/', null, $v[0]);
                        $c = 'NOT IN';
                    }

                    // Cast all ID's to integers. (RE: #2191)
                    $v = array_map(function ($val) {
                        $val = \General::intval($val);

                        // \General::intval can return -1, so reset that to 0
                        // so there are no side effects for the following
                        // array_sum and array_filter calls. RE: #2475
                        if (-1 === $val) {
                            $val = 0;
                        }

                        return $val;
                    }, $v);
                    $count = array_sum($v);
                    $v = array_filter($v);

                    // If the ID was cast to 0, then we need to filter on 'id' = 0,
                    // which will of course return no results, but without it the
                    // Datasource will return ALL results, which is not the
                    // desired behaviour. RE: #1619
                    if (0 === $count) {
                        $v[] = 0;
                    }

                    // If there are no ID's, no need to filter. RE: #1567
                    if (!empty($v)) {
                        $where .= ' AND `e`.id '.$c.' ('.implode(', ', $v).') ';
                    }
                }
            } elseif ('system:creation-date' === $field_id || 'system:modification-date' === $field_id || 'system:date' === $field_id) {
                if ('system:date' === $field_id && \Symphony::Log()) {
                    \Symphony::Log()->pushDeprecateWarningToLog('system:date', 'system:creation-date` or `system:modification-date', array(
                        'message-format' => __('The `%s` data source filter is deprecated.'),
                    ));
                }
                $date_joins = '';
                $date_where = '';
                $date = new \FieldDate();
                $date->buildDSRetrievalSQL($value, $date_joins, $date_where, (self::FILTER_AND == $filter_type ? true : false));

                // Replace the date field where with the `creation_date` or `modification_date`.
                $date_where = preg_replace('/`t\d+`.date/', ('system:modification-date' !== $field_id) ? '`e`.creation_date_gmt' : '`e`.modification_date_gmt', $date_where);
                $where .= $date_where;
            } else {
                if (!self::$_fieldPool[$field_id]->buildDSRetrievalSQL($value, $joins, $where, (self::FILTER_AND == $filter_type ? true : false))) {
                    $this->_force_empty_result = true;

                    return;
                }

                if (!$group) {
                    $group = self::$_fieldPool[$field_id]->requiresSQLGrouping();
                }
            }
        }
    }

    public function execute(array &$param_pool = null)
    {
        $result = new \XMLElement($this->dsParamROOTELEMENT);
        $this->_param_pool = $param_pool;
        $where = null;
        $joins = null;
        $group = false;

        if (!$section = \SectionManager::fetch((int) $this->getSource())) {
            $about = $this->about();
            trigger_error(__('The Section, %s, associated with the Data source, %s, could not be found.', array($this->getSource(), '<code>'.$about['name'].'</code>')), E_USER_ERROR);
        }

        $sectioninfo = new \XMLElement('section', \General::sanitize($section->get('name')), array(
            'id' => $section->get('id'),
            'handle' => $section->get('handle'),
        ));

        if (true == $this->_force_empty_result) {
            if ('yes' === $this->dsParamREDIRECTONREQUIRED) {
                throw new \FrontendPageNotFoundException();
            }

            $this->_force_empty_result = false; //this is so the section info element doesn't disappear.
            $error = new \XMLElement('error', __('Data source not executed, required parameter is missing.'), array(
                'required-param' => $this->dsParamREQUIREDPARAM,
            ));
            $result->appendChild($error);
            $result->prependChild($sectioninfo);

            return $result;
        }

        if (true == $this->_negate_result) {
            if ('yes' === $this->dsParamREDIRECTONFORBIDDEN) {
                throw new \FrontendPageNotFoundException();
            }

            $this->_negate_result = false; //this is so the section info element doesn't disappear.
            $result = $this->negateXMLSet();
            $result->prependChild($sectioninfo);

            return $result;
        }

        if (is_array($this->dsParamINCLUDEDELEMENTS)) {
            $include_pagination_element = in_array('system:pagination', $this->dsParamINCLUDEDELEMENTS);
        } else {
            $this->dsParamINCLUDEDELEMENTS = [];
        }

        if (isset($this->dsParamPARAMOUTPUT) && !is_array($this->dsParamPARAMOUTPUT)) {
            $this->dsParamPARAMOUTPUT = array($this->dsParamPARAMOUTPUT);
        }

        $this->_can_process_system_parameters = $this->canProcessSystemParameters();

        if (!isset($this->dsParamPAGINATERESULTS)) {
            $this->dsParamPAGINATERESULTS = 'yes';
        }

        // Process Filters
        $this->processFilters($where, $joins, $group);

        // Process Sorting
        if ('system:id' == $this->dsParamSORT) {
            \EntryManager::setFetchSorting('system:id', $this->dsParamORDER);
        } elseif ('system:modification-date' == $this->dsParamSORT) {
            \EntryManager::setFetchSorting('system:modification-date', $this->dsParamORDER);
        } else {
            \EntryManager::setFetchSorting(
                \FieldManager::fetchFieldIDFromElementName($this->dsParamSORT, $this->getSource()),
                $this->dsParamORDER
            );
        }

        // combine `INCLUDEDELEMENTS`, `PARAMOUTPUT` and `GROUP` into an
        // array of field handles to optimise the `EntryManager` queries
        $datasource_schema = $this->dsParamINCLUDEDELEMENTS;

        if (is_array($this->dsParamPARAMOUTPUT)) {
            $datasource_schema = array_merge($datasource_schema, $this->dsParamPARAMOUTPUT);
        }

        if ($this->dsParamGROUP) {
            $datasource_schema[] = \FieldManager::fetchHandleFromID($this->dsParamGROUP);
        }

        $entries = \EntryManager::fetchByPage(
            ('yes' === $this->dsParamPAGINATERESULTS && $this->dsParamSTARTPAGE > 0 ? $this->dsParamSTARTPAGE : 1),
            $this->getSource(),
            ('yes' === $this->dsParamPAGINATERESULTS && $this->dsParamLIMIT >= 0 ? $this->dsParamLIMIT : null),
            $where,
            $joins,
            $group,
            (!$include_pagination_element ? true : false),
            true,
            array_unique($datasource_schema)
        );

        /*
         * Immediately after building entries allow modification of the Data Source entries array
         *
         * @delegate DataSourceEntriesBuilt
         * @param string $context
         * '/frontend/'
         * @param Datasource $datasource
         * @param array $entries
         * @param array $filters
         */
        \Symphony::ExtensionManager()->notifyMembers('DataSourceEntriesBuilt', '/frontend/', array(
            'datasource' => &$this,
            'entries' => &$entries,
            'filters' => $this->dsParamFILTERS,
        ));

        $entries_per_page = ('yes' === $this->dsParamPAGINATERESULTS && isset($this->dsParamLIMIT) && $this->dsParamLIMIT >= 0 ? $this->dsParamLIMIT : $entries['total-entries']);

        if (($entries['total-entries'] <= 0 || true === $include_pagination_element) && (!is_array($entries['records']) || empty($entries['records'])) || '0' == $this->dsParamSTARTPAGE) {
            if ('yes' === $this->dsParamREDIRECTONEMPTY) {
                throw new \FrontendPageNotFoundException();
            }

            $this->_force_empty_result = false;
            $result = $this->emptyXMLSet();
            $result->prependChild($sectioninfo);

            if ($include_pagination_element) {
                $pagination_element = \General::buildPaginationElement(0, 0, $entries_per_page);

                if ($pagination_element instanceof \XMLElement && $result instanceof \XMLElement) {
                    $result->prependChild($pagination_element);
                }
            }
        } else {
            if (!$this->_param_output_only) {
                $result->appendChild($sectioninfo);

                if ($include_pagination_element) {
                    $pagination_element = \General::buildPaginationElement(
                        $entries['total-entries'],
                        $entries['total-pages'],
                        $entries_per_page,
                        ('yes' === $this->dsParamPAGINATERESULTS && $this->dsParamSTARTPAGE > 0 ? $this->dsParamSTARTPAGE : 1)
                    );

                    if ($pagination_element instanceof \XMLElement && $result instanceof \XMLElement) {
                        $result->prependChild($pagination_element);
                    }
                }
            }

            // If this datasource has a Limit greater than 0 or the Limit is not set
            if (!isset($this->dsParamLIMIT) || $this->dsParamLIMIT > 0) {
                if (!isset($this->dsParamASSOCIATEDENTRYCOUNTS) || 'yes' === $this->dsParamASSOCIATEDENTRYCOUNTS) {
                    $this->_associated_sections = $section->fetchChildAssociations();
                }

                // If the datasource require's GROUPING
                if (isset($this->dsParamGROUP)) {
                    self::$_fieldPool[$this->dsParamGROUP] = \FieldManager::fetch($this->dsParamGROUP);

                    if (null == self::$_fieldPool[$this->dsParamGROUP]) {
                        throw new \SymphonyErrorPage(vsprintf("The field used for grouping '%s' cannot be found.", $this->dsParamGROUP));
                    }

                    $groups = self::$_fieldPool[$this->dsParamGROUP]->groupRecords($entries['records']);

                    foreach ($groups as $element => $group) {
                        foreach ($group as $g) {
                            $result->appendChild(
                                $this->processRecordGroup($element, $g)
                            );
                        }
                    }
                } else {
                    if (isset($entries['records'][0])) {
                        $data = $entries['records'][0]->getData();
                        $pool = \FieldManager::fetch(array_keys($data));
                        self::$_fieldPool += $pool;
                    }

                    foreach ($entries['records'] as $entry) {
                        $xEntry = $this->processEntry($entry);

                        if ($xEntry instanceof \XMLElement) {
                            $result->appendChild($xEntry);
                        }
                    }
                }
            }
        }

        $param_pool = $this->_param_pool;

        return $result;
    }
}