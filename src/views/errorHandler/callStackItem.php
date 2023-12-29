<?php
use trinity\http\ErrorHandlerHttp;

/**
 * @var string|null $file
 * @var int|null $line
 * @var string|null $class
 * @var string|null $method
 * @var int $index
 * @var string[] $lines
 * @var int $begin
 * @var int $end
 * @var array $args
 * @var ErrorHandlerHttp $handler
 */
?>
<li class="<?= $handler->isCoreFile($file) === false ? 'application' : '' ?>  call-stack-item" data-line="<?= ($line - $begin) ?> ">
    <div class="element-wrap">
        <div class="element">
            <span class="item-number"><?= $index ?>.</span>
            <?php if ($file !== null): ?>
                <span class="text">in <?= $handler->htmlEncode($file) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php if (empty($lines) === false): ?>
        <div class="code-wrap">
            <div class="error-line"></div>
            <?php for ($i = $begin; $i <= $end; ++$i): ?><div class="hover-line"></div><?php endfor; ?>
            <div class="code">
                <?php for ($i = $begin; $i <= $end; ++$i): ?><span class="lines-item"><?= ($i + 1) ?></span><?php endfor; ?>
                <pre><?php
                    for ($i = $begin; $i <= $end; ++$i) {
                        echo (trim($lines[$i]) === '') ? " \n" : $handler->htmlEncode($lines[$i]);
                    }
                    ?></pre>
            </div>
        </div>
    <?php endif; ?>
</li>
