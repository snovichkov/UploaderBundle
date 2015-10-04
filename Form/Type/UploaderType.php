<?php

namespace CyberApp\UploaderBundle\Form\Type;

use CyberApp\UploaderBundle\Form\DataTransformer\MultipleTransformer;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UploaderType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContainerInterface
     */
    protected $serviceContainer;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @param ContainerInterface $serviceContainer      Service container
     * @param null                $translationDomain    Translation domain
     */
    public function __construct(ContainerInterface $serviceContainer, $translationDomain = null)
    {
        $this->translator = $serviceContainer->get('translator');
        $this->serviceContainer = $serviceContainer;
        $this->translationDomain = $translationDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addViewTransformer(new MultipleTransformer());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // globals
        $t = $this->translator;
        $c = $this->serviceContainer;
        $d = $this->translationDomain;

        // messages
        $messages = [
            'maxFileSize' => $t->trans('uploader.error.maxsize', [], $d),
            'minFileSize' => $t->trans('uploader.error.minsize', [], $d),
            'acceptFileTypes' => $t->trans('uploader.error.invalidfile', [], $d),
            'maxNumberOfFiles' => $t->trans('uploader.error.maxfiles', [], $d),
            'error.unknown' => $t->trans('uploader.error.unknown', [], $d),
            'error.maxsize' => $t->trans('uploader.error.maxsize', [], $d),
            'error.whitelist' => $t->trans('uploader.error.invalidfile', [], $d),
            'error.blacklist' => $t->trans('uploader.error.invalidfile', [], $d),
        ];

        // build uploader configuration
        $params = $c->getParameter('oneup_uploader.config.' . $options['endpoint']);
        $config = [
            'dataType' => 'json',
            'autoUpload' => true,
            'maxChunkSize' => 1000000,
            'messages' => &$messages,
        ];

        if (isset($params['max_size']) && $params['max_size']) {
            $config['maxFileSize'] = $params['max_size'];
        }

        $acceptFileTypes = null;

        if (isset($params['allowed_mimetypes']) && count($params['allowed_mimetypes']) > 0) {
            $extensions = $this->mimeTypesToExtensions($params['allowed_mimetypes']);

            $acceptFileTypes = sprintf('/(\.|\/)(%s)$/i', implode('|', $extensions));
            $messages['acceptFileTypes'] = $messages['error.whitelist'] = $t->trans('uploader.error.whitelist', [
                '%formats' => implode(', ', $extensions),
            ], $d);
        }

        if (isset($params['disallowed_mimetypes']) && count($params['disallowed_mimetypes']) > 0) {
            $extensions = $this->mimeTypesToExtensions($params['disallowed_mimetypes']);

            $acceptFileTypes = sprintf('/(\.|\/)(?!(%s)$)/i', implode('|', $extensions));
            $messages['acceptFileTypes'] = $messages['error.blacklist'] = $t->trans('uploader.error.blacklist', [
                '%formats' => implode(', ', $extensions),
            ], $d);
        }

        // view vars
        $view->vars = array_merge($view->vars, [
            'options' => array_merge($config, $options['config']),
            'messages' => $messages,
            'multiple' => $options['multiple'],
            'endpoint' => $options['endpoint'],
            'uri_prefix' => $options['uri_prefix'],
            'accept_file_types' => $acceptFileTypes,
        ]);

        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $uriPrefixNormalizer = function (OptionsResolver $resolver, $uriPrefix) {
            return rtrim($uriPrefix, '/') . '/';
        };

        $resolver
            ->setDefaults([
                'config' => [],
                'messages' => [],
                'multiple' => false,
            ])
            ->setRequired(['endpoint', 'uri_prefix', ])
            ->setAllowedTypes([
                'config' => 'array',
                'messages' => 'array',
                'multiple' => 'bool',
                'endpoint' => 'string',
                'uri_prefix' => ['null', 'string'],
            ])
            ->setNormalizer('uri_prefix', $uriPrefixNormalizer);
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'uploader';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * Convert mime type to extension
     *
     * @param array $mimeTypes Mime types to conversation
     *
     * @return array
     */
    protected function mimeTypesToExtensions($mimeTypes)
    {
        return array_filter(array_map(function ($mimeType) {
            return ExtensionGuesser::getInstance()->guess($mimeType);
        }, $mimeTypes), 'strlen');
    }
}
