<?php

namespace App\Serializer;

use App\Entity\Scheme;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class SchemeNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private StorageInterface    $storage
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /* @var Scheme $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['image'] = $this->storage->resolveUri($object, 'imageFile');

        $path = $this->storage->resolvePath($object, 'imageFile');
        if ($path && is_file($path) && is_readable($path)) {
            [$w, $h] = getimagesize($path);
            $data['imageDimensionX'] = $w;
            $data['imageDimensionY'] = $h;
        } else {
            $data['imageDimensionX'] = null;
            $data['imageDimensionY'] = null;
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Scheme;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ Scheme::class => true ];
    }
}
