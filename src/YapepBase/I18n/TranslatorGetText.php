<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   I18n
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\I18n;


use YapepBase\Exception\I18n\ParameterException;
use YapepBase\Exception\I18n\TranslationNotFoundException;

/**
 * Internationalization class, that translates strings to other languages using gettext.
 *
 * @link http://php.net/gettext
 *
 * @package YapepBase\I18n
 */
class TranslatorGetText implements ITranslator {

	/**
	 * Current Locale.
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * Domain name of the translation.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Path of the translation files.
	 *
	 * @var string
	 */
	protected $translationPath;

	/**
	 * Separator string of the parameters.
	 *
	 * @var string
	 */
	protected $paramSeparator;

	/**
	 * Encoding of the translated text.
	 *
	 * @var string
	 */
	protected $encoding;

	/**
	 * Constructor.
	 *
	 * @param string $locale            Locale to use.
	 * @param string $domain            Domain name.
	 * @param string $translationPath   Path of the translations.
	 * @param string $paramSeparator    Separator string of the parameters.
	 * @param string $encoding          Encoding of the translated text.
	 */
	function __construct($locale, $domain, $translationPath, $paramSeparator, $encoding = 'UTF-8') {
		putenv('LANG=' . $locale);
		setlocale(LC_ALL, $locale);
		bindtextdomain($domain, $translationPath);
		bind_textdomain_codeset($domain, $encoding);
		textdomain($domain);

		$this->locale          = $locale;
		$this->domain          = $domain;
		$this->translationPath = $translationPath;
		$this->paramSeparator  = $paramSeparator;
		$this->encoding        = $encoding;
	}

	/**
	 * Translates the string.
	 *
	 * @param string $messageId   The string to translate.
	 * @param array  $params      Associative array with parameters for the translation. The key is the param name.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\I18n\TranslationNotFoundException   If the error mode is set to exception.
	 * @throws \YapepBase\Exception\I18n\ParameterException             If there are problems with the parameters.
	 */
	public function translate($messageId, array $params = array()) {
		$result = $this->substituteParameters(_($messageId), $params);

		if ($result == $messageId) {
			throw new TranslationNotFoundException('The given messageId "' . $messageId . '" is not translated');
		}
		return $result;
	}

	/**
	 * Substitutes the parameter names with the values in the provided string
	 *
	 * @param string $string   The string to translate.
	 * @param array  $params   Associative array with parameters for the translation. The key is the param name.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\I18n\ParameterException
	 */
	protected function substituteParameters($string, array $params) {
		foreach ($params as $paramName => $paramValue) {
			$replacementCount = 0;
			$string = str_replace($this->paramSeparator . $paramName . $this->paramSeparator, $paramValue, $string,
				$replacementCount);

			if ($replacementCount == 0) {
				throw new ParameterException('Parameter with name "' . $paramName . '" not found in provided string');
			}
		}

		$paramSeparatorEscaped = preg_quote($this->paramSeparator, '#');
		$pattern = '#' . $paramSeparatorEscaped . '[-_a-zA-Z0-9]+' . $paramSeparatorEscaped . '#';

		if (preg_match($pattern, $string)) {
			throw new ParameterException('Not all parameters are set for the translated string');
		}

		return $string;
	}

	/**
	 * Returns the current locale.
	 *
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * Returns the current translation domain.
	 *
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * Returns the current path of the translated files.
	 *
	 * @return string
	 */
	public function getTranslationPath() {
		return $this->translationPath;
	}

	/**
	 * Returns the current param separator.
	 *
	 * @return string
	 */
	public function getParamSeparator() {
		return $this->paramSeparator;
	}

	/**
	 * Returns the current encoding of the translations.
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}
}