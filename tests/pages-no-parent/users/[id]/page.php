<?php

declare(strict_types=1);

return function (array $params) {
  yield 'My name is John Doe with the id: ' . $params['id'];
};