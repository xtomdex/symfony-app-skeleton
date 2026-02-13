<?php

declare(strict_types=1);

namespace App\Infrastructure\Form;

use Symfony\Component\Form\FormInterface;

final class FormErrorNormalizer
{
    /**
     * @return array{message: string, errors: array<string, string>}
     */
    public function normalize(FormInterface $form): array
    {
        $errorsTree = $this->collectErrors($form);

        $result = [
            'message' => $errorsTree['message'] ?? 'Validation failed',
            'errors' => [],
        ];

        foreach ($errorsTree as $field => $messages) {
            if ($field === 'message') {
                continue;
            }

            // Flatten nested errors to a single string (first message).
            $result['errors'][$field] = $this->firstMessage($messages);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function collectErrors(FormInterface $form): array
    {
        $errors = $form->isRoot() ? ['message' => ''] : [];

        foreach ($form->getErrors() as $error) {
            if ($form->isRoot()) {
                $errors['message'] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->collectErrors($child);
            }
        }

        return $errors;
    }

    private function firstMessage(mixed $node): string
    {
        if (is_string($node)) {
            return $node;
        }

        if (is_array($node)) {
            // If nested associative array, try to find first leaf message.
            foreach ($node as $k => $v) {
                if ($k === 'message') {
                    continue;
                }
                $msg = $this->firstMessage($v);
                if ($msg !== '') {
                    return $msg;
                }
            }

            // Numeric array case
            $first = $node[0] ?? '';
            return is_string($first) ? $first : '';
        }

        return '';
    }
}
