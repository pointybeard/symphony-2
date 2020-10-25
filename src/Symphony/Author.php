<?php

//declare(strict_types=1);

namespace Symphony\Symphony;

/**
 * The Author class represents a Symphony Author object. Authors are
 * the backend users in Symphony.
 */
class Author
{
    /**
     * An associative array of information relating to this author where
     * the keys map directly to the `tbl_authors` columns.
     *
     * @var array
     */
    private $fields = [];

    /**
     * Stores a key=>value pair into the Author object's `$this->fields` array.
     *
     * @param string $field
     *                      Maps directly to a column in the `tbl_authors` table
     * @param string $value
     *                      The value for the given $field
     */
    public function set($field, $value)
    {
        $this->fields[trim($field)] = trim($value);
    }

    /**
     * Retrieves the value from the Author object by field from `$this->fields`
     * array. If field is omitted, all fields are returned.
     *
     * @param string $field
     *                      Maps directly to a column in the `tbl_authors` table. Defaults to null
     *
     * @return mixed
     *               If the field is not set or is empty, returns null.
     *               If the field is not provided, returns the `$this->fields` array
     *               Otherwise returns a string.
     */
    public function get($field = null)
    {
        if (null === $field) {
            return $this->fields;
        }

        if (!isset($this->fields[$field]) || '' == $this->fields[$field]) {
            return null;
        }

        return $this->fields[$field];
    }

    /**
     * Given a field, remove it from `$this->fields`.
     *
     * @since Symphony 2.2.1
     *
     * @param string $field
     *                      Maps directly to a column in the `tbl_authors` table. Defaults to null
     */
    public function remove($field = null)
    {
        if (null !== $field) {
            return;
        }

        unset($this->fields[$field]);
    }

    /**
     * Returns boolean if the current Author is the original creator
     * of this Symphony installation.
     *
     * @return bool
     */
    public function isPrimaryAccount()
    {
        return 'yes' === $this->get('primary');
    }

    /**
     * Returns boolean if the current Author is of the developer
     * user type.
     *
     * @return bool
     */
    public function isDeveloper()
    {
        return 'developer' == $this->get('user_type');
    }

    /**
     * Returns boolean if the current Author is of the manager
     * user type.
     *
     * @since  2.3.3
     *
     * @return bool
     */
    public function isManager()
    {
        return 'manager' == $this->get('user_type');
    }

    /**
     * Returns boolean if the current Author is of the author
     * user type.
     *
     * @since  2.4
     *
     * @return bool
     */
    public function isAuthor()
    {
        return 'author' == $this->get('user_type');
    }

    /**
     * Returns boolean if the current Author's authentication token
     * is active or not.
     *
     * @return bool
     */
    public function isTokenActive()
    {
        return 'yes' === $this->get('auth_token_active') ? true : false;
    }

    /**
     * A convenience method that returns an Authors full name.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->get('first_name').' '.$this->get('last_name');
    }

    /**
     * Creates an author token using the `Cryptography::hash` function and the
     * current Author's username and password. The default hash function
     * is SHA1.
     *
     * @see toolkit.Cryptography#hash()
     * @see toolkit.General#substrmin()
     *
     * @return string
     */
    public function createAuthToken()
    {
        return \General::substrmin(sha1($this->get('username').$this->get('password')), 8);
    }

    /**
     * Prior to saving an Author object, the validate function ensures that
     * the values in `$this->fields` array are correct. As of Symphony 2.3
     * Authors must have unique username AND email address. This function returns
     * boolean, with an `$errors` array provided by reference to the callee
     * function.
     *
     * @param array $errors
     *
     * @return bool
     */
    public function validate(&$errors)
    {
        $errors = [];
        $current_author = null;

        if (null === $this->get('first_name')) {
            $errors['first_name'] = __('First name is required');
        }

        if (null === $this->get('last_name')) {
            $errors['last_name'] = __('Last name is required');
        }

        if ($this->get('id')) {
            $current_author = \Symphony::Database()->fetchRow(0, sprintf(
                'SELECT `email`, `username`
                FROM `tbl_authors`
                WHERE `id` = %d',
                $this->get('id')
            ));
        }

        // Include validators
        include DOCROOT.'/src/Includes/Validators.php';

        // Check that Email is provided
        if (null === $this->get('email')) {
            $errors['email'] = __('E-mail address is required');

        // Check Email is valid
        } elseif (isset($validators['email']) && !\General::validateString($this->get('email'), $validators['email'])) {
            $errors['email'] = __('E-mail address entered is invalid');

        // Check Email is valid, fallback when no validator found
        } elseif (!isset($validators['email']) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('E-mail address entered is invalid');

        // Check that if an existing Author changes their email address that
            // it is not already used by another Author
        } elseif ($this->get('id')) {
            if (
                $current_author['email'] !== $this->get('email') &&
                0 != \Symphony::Database()->fetchVar('count', 0, sprintf(
                    "SELECT COUNT(`id`) as `count`
                    FROM `tbl_authors`
                    WHERE `email` = '%s'",
                    \Symphony::Database()->cleanValue($this->get('email'))
                ))
            ) {
                $errors['email'] = __('E-mail address is already taken');
            }

            // Check that Email is not in use by another Author
        } elseif (\Symphony::Database()->fetchVar('id', 0, sprintf(
            "SELECT `id`
            FROM `tbl_authors`
            WHERE `email` = '%s'
            LIMIT 1",
            \Symphony::Database()->cleanValue($this->get('email'))
        ))) {
            $errors['email'] = __('E-mail address is already taken');
        }

        // Check the username exists
        if (null === $this->get('username')) {
            $errors['username'] = __('Username is required');

        // Check that if it's an existing Author that the username is not already
        // in use by another Author if they are trying to change it.
        } elseif ($this->get('id')) {
            if (
                $current_author['username'] !== $this->get('username') &&
                0 != \Symphony::Database()->fetchVar('count', 0, sprintf(
                    "SELECT COUNT(`id`) as `count`
                    FROM `tbl_authors`
                    WHERE `username` = '%s'",
                    \Symphony::Database()->cleanValue($this->get('username'))
                ))
            ) {
                $errors['username'] = __('Username is already taken');
            }

            // Check that the username is unique
        } elseif (\Symphony::Database()->fetchVar('id', 0, sprintf(
            "SELECT `id`
            FROM `tbl_authors`
            WHERE `username` = '%s'
            LIMIT 1",
            \Symphony::Database()->cleanValue($this->get('username'))
        ))) {
            $errors['username'] = __('Username is already taken');
        }

        if (null === $this->get('password')) {
            $errors['password'] = __('Password is required');
        }

        return empty($errors) ? true : false;
    }

    /**
     * This is the insert method for the Author. This takes the current
     * `$this->fields` values and adds them to the database using either the
     * `\AuthorManager::edit` or `\AuthorManager::add` functions. An
     * existing user is determined by if an ID is already set.
     * When the database is updated successfully, the id of the author is set.
     *
     * @see toolkit.AuthorManager#add()
     * @see toolkit.AuthorManager#edit()
     *
     * @return int|bool
     *                  When a new Author is added or updated, an integer of the Author ID
     *                  will be returned, otherwise false will be returned for a failed update
     */
    public function commit()
    {
        if (null !== $this->get('id')) {
            $id = $this->get('id');
            $this->remove('id');

            if (\AuthorManager::edit($id, $this->get())) {
                $this->set('id', $id);

                return $id;
            } else {
                return false;
            }
        } else {
            $id = \AuthorManager::add($this->get());
            if ($id) {
                $this->set('id', $id);
            }

            return $id;
        }
    }
}
