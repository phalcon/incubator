<?php
namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

/**
 * ResourceBundle adapter
 */
class ResourceBundle extends Base implements AdapterInterface
{

    /**
     * @var \ResourceBundle
     */
    protected $bundle;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var boolean
     */
    protected $fallback = true;

    /**
     * Class constructor
     *
     * @param  array $options
     *
     * @throws \Phalcon\Translate\Exception
     */
    public function __construct($options)
    {
        if (!is_array($options)) {
            throw new Exception('Invalid options');
        }

        if (!class_exists('\ResourceBundle')) {
            throw new Exception('"ResourceBundle" class is required');
        }

        if (!class_exists('\MessageFormatter')) {
            throw new Exception('"MessageFormatter" class is required');
        }

        if (!isset($options['bundle'])) {
            throw new Exception('"bundle" option is required');
        }

        if (!isset($options['locale'])) {
            throw new Exception('"locale" option is required');
        }

        if (isset($options['fallback'])) {
            $this->fallback = (bool) $options['fallback'];
        }

        $this->options = $options;
        $this->bundle  = new \ResourceBundle($this->options['locale'], $this->options['bundle'], $this->fallback);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $index
     */
    public function exists($index)
    {
        if (false !== $this->get($index, $this->bundle)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param string     $index
     * @param null|array $placeholders
     */
    public function query($index, $placeholders = null)
    {
        if (!$this->exists($index)) {
            return $index;
        }

        $formatter = new \MessageFormatter($this->options['locale'], $this->get($index, $this->bundle));

        if (null !== $formatter) {
            return $formatter->format((array) $placeholders);
        } else {
            return $index;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string     $translateKey
     * @param array|null $placeholders
     */
    public function t($translateKey, $placeholders = null)
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Getting a translation
     *
     * @example               $this->get('labels.form.new')
     * @param                 $key
     * @param \ResourceBundle $bundle
     *
     * @return mixed|\ResourceBundle
     */
    public function get($key, $bundle)
    {
        $keyPath = explode(".", $key);

        if ($bundle instanceof \ResourceBundle && !empty($keyPath)) {
            $bundle = $bundle->get($keyPath[0]);

            if (is_object($bundle)) {
                array_shift($keyPath);
                $keyValue = implode('.', $keyPath);

                return $this->get($keyValue, $bundle);
            } else {
                return $bundle;
            }
        }

        return false;
    }
}
