<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Ldap
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Ldap;

use YapepBase\Exception\LdapAddException;
use YapepBase\Exception\LdapBindException;
use YapepBase\Exception\LdapConnectionException;
use YapepBase\Exception\LdapDeleteException;
use YapepBase\Exception\LdapModifyException;
use YapepBase\Exception\LdapSearchException;

/**
 * This is a single LDAP connection. It supports only LDAPv3 servers.
 *
 * @package    YapepBase
 * @subpackage Ldap
 *
 * @todo Add explanation and fix param types at __construct() and connect()
 */
class LdapConnection {

	/**
	 * The connection link.
	 */
	protected $link;

	/**
	 * Never dereference aliases.
	 */
	const DEREF_NEVER = LDAP_DEREF_NEVER;
	/**
	 * Aliases should be dereferenced during the search but not when locating the base object of the search.
	 */
	const DEREF_SEARCH = LDAP_DEREF_SEARCHING;
	/**
	 * Aliases should be dereferenced when locating the base object but not during the search.
	 */
	const DEREF_FIND = LDAP_DEREF_FINDING;
	/**
	 * Aliases should be dereferenced always.
	 */
	const DEREF_ALWAYS = LDAP_DEREF_ALWAYS;

	/**
	 * Searches the LDAP with the subtree option.
	 */
	const SCOPE_SUB = 1;
	/**
	 * Searches one level of the LDAP tree.
	 */
	const SCOPE_ONE = 0;

	/**
	 * If the function has parameters, connects to the LDAP server.
	 *
	 * @param string $hostname
	 * @param int    $port
	 */
	public function __construct($hostname = false, $port = false) {
		if ($hostname) {
			$this->connect($hostname, $port);
		}
	}

	/**
	 * Closes the link.
	 */
	public function __destruct() {
		if ($this->link) {
			$this->disconnect();
		}
	}

	/**
	 * Explicitly connects the LDAP server
	 *
	 * @param string $hostname
	 * @param int    $port
	 */
	public function connect($hostname = false, $port = false) {
		if ($hostname && $port) {
			$link = @ldap_connect($hostname, $port);
		} else if ($hostname) {
			$link = @ldap_connect($hostname);
		} else {
			$link = @ldap_connect();
		}

		if (!$link) {
			throw new LdapConnectionException($link);
		}

		$this->link = $link;
		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
	}

	/**
	 * Disconnects from the LDAP server.
	 */
	public function disconnect() {
		@ldap_close($this->link);
		$this->link = null;
	}

	/**
	 * Binds (authenticates) with the LDAP server. Pass empty parameters to do an anonymous bind.
	 *
	 * @param \YapepBase\Ldap\LdapDn $rdn
	 * @param string                 $password
	 */
	public function bind(LdapDn $rdn = null, $password = '') {
		if (!$this->link) {
			$this->connect();
		}

		if ($rdn) {
			if ($password) {
				$bind = @ldap_bind($this->link, (string)$rdn, $password);
			} else {
				$bind = @ldap_bind($this->link, (string)$rdn);
			}

			if (!$bind) {
				throw new LdapBindException($this->link);
			}
		} else {
			$bind = @ldap_bind($this->link);
			if (!$bind) {
				throw new LdapBindException($this->link);
			}
		}
	}

	/**
	 * Unbinds (deauthenticates) from an LDAP server. Useful, if you want to change users within a connection.
	 */
	public function unbind() {
		if (!ldap_unbind($this->link)) {
			throw new LdapBindException($this->link);
		}
	}

	/**
	 * Runs an LDAP add operation on the server.
	 *
	 * @param \YapepBase\Ldap\LdapDn    $dn      The distinguised name of the entry.
	 * @param \YapepBase\Ldap\LdapEntry $entry   The data in the entry.
	 *
	 * @throws \YapepBase\Exception\LdapAddException   If the add fails
	 */
	public function add(LdapDn $dn, LdapEntry $entry) {
		if (!$this->link) {
			$this->connect();
		}

		$result = @ldap_add($this->link, (string)$dn, $entry->getAsArray());

		if (!$result) {
			throw new LdapAddException($this->link);
		}
	}

	/**
	 * Runs an LDAP modify operation on the server.
	 *
	 * @param \YapepBase\Ldap\LdapDn    $dn     The distinguised name of the entry.
	 * @param \YapepBase\Ldap\LdapEntry $entry  The data in the entry.
	 *
	 * @throws \YapepBase\Exception\LdapModifyException   If the modify fails.
	 */
	function modify(LdapDn $dn, LdapEntry $entry) {
		if (!$this->link) {
			$this->connect();
		}

		$result = @ldap_modify($this->link, (string)$dn, $entry->getAsArray());

		if (!$result) {
			throw new LdapModifyException($this->link);
		}
	}

	/**
	 * Deletes an entry from the LDAP.
	 *
	 * @param \YapepBase\Ldap\LdapDn $dn
	 *
	 * @throws \YapepBase\Exception\LdapDeleteException   If the deletion fails.
	 */
	function delete(LdapDn $dn) {
		if (!$this->link) {
			$this->connect();
		}

		$result = @ldap_delete($this->link, (string)$dn);

		if (!$result) {
			throw new LdapDeleteException($this->link);
		}
	}

	/**
	 * Decodes a hexadecimal character.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function decodeHex($value) {
	    return chr(hexdec($value));
	}

	/**
	 * Decode a hexadecimal value.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function decode($value) {
		// TODO: Is the " really necessary? You should change this to ' [emul]
	    return preg_replace("/(\x([0-9A-Fa-f][0-9A-Fa-f]))/e", "\$this->decodeHex('\\2')", $value);
	}

	/**
	 * Postprocesses an LDAP entry. (Decodes hex characters, etc.)
	 *
	 * @param array|string $data
	 *
	 * @return  array|string
	 */
	protected function postprocess($data) {
	    if (is_array($data)) {
			if (isset($data['count'])) {
				unset($data['count']);
			}
			foreach ($data as $key => $value) {
				$data[$key] = $this->postprocess($value);
			}
	    } else {
			$data = $this->decode($data);
	    }
	    return $data;
	}

	/**
	 * Runs a search operation on the LDAP server and returns the result.
	 *
	 * @param \YapepBase\Ldap\LdapDn $rootdn
	 * @param string                 $filter         The filter with params encoded as :_placeholder.
	 * @param array                  $filterParams
	 * @param array                  $attributes     Attributes to request. Optional.
	 * @param int                    $deref          Dereference options. Use self::DEREF_*
	 * @param int                    $scope          Scoping options. Use self::SCOPE_*
	 *
	 * @todo Unfinished, usage of the $deref should be implemented later [emul]
	 *
	 * @return  array
	 *
	 * @throws \YapepBase\Exception\LdapSearchException   If an error occured during the ldap query process.
	 */
	public function search(LdapDn $rootdn, $filter, $filterParams = array(), $attributes = array(),
		$deref = self::DEREF_NEVER, $scope = self::SCOPE_SUB) {

	    if (!$this->link) {
			$this->connect();
	    }
	    foreach ($filterParams as $key => $value) {
			unset($filterParams[$key]);
			$filterParams[':_' . $key] = $value;
	    }
	    if ($scope == self::SCOPE_SUB) {
			$result = @ldap_search($this->link, (string)$rootdn, strtr($filter, $filterParams), $attributes);
	    } else {
			$result = @ldap_list($this->link, (string)$rootdn, strtr($filter, $filterParams), $attributes);
	    }
		$result = @ldap_get_entries($this->link, $result);
		$result = $this->postprocess($result);
		if ($result === false) {
			throw new LdapSearchException($this->link);
		}
		return $result;
	}
}
