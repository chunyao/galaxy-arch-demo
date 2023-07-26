<?php declare(strict_types=1);

namespace Mabang\Galaxy\Common\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class Autowired
 *
 * @Annotation
 * @Target("PROPERTY")
 * @Attributes({
 *     @Attribute("name", type="string")
 * })
 *
 * @since 2.0
 */
final class Autowired
{
    /**
     * Bean name
     *
     * @var string
     */
    private $name = 'Autowired';

    /**
     * Inject constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->name = $values['value'];
        }
        if (isset($values['name'])) {
            $this->name = $values['name'];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
