<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use ApiClientBundle\Model\AbstractClientConfiguration;

class <?= $class_name; ?> extends AbstractClientConfiguration<?= "\n" ?>
{
    public function domain(): string
    {
        return '<?= $domain?>';
    }

    public function scheme(): string
    {
        return '<?= $scheme?>';
    }

    public function isAsync(): bool
    {
        return <?= $isAsync ?>;
    }
}
