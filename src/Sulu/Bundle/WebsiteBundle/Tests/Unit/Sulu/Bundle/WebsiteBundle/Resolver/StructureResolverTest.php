<?php

namespace Sulu\Bundle\WebsiteBundle\Resolver;

use Prophecy\Argument;
use Sulu\Component\Content\ContentTypeInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;
use Prophecy\PhpUnit\ProphecyTestCase;
use Sulu\Component\Content\StructureManagerInterface;

class StructureResolverTest extends ProphecyTestCase
{
    /**
     * @var StructureResolverInterface
     */
    private $structureResolver;

    /**
     * @var ContentTypeManagerInterface
     */
    private $contentTypeManager;

    /**
     * @var ContentTypeInterface
     */
    private $contentType;

    /**
     * @var StructureManagerInterface
     */
    private $structureManager;

    public function setUp()
    {
        parent::setUp();

        $this->contentTypeManager = $this->prophesize('Sulu\Component\Content\ContentTypeManagerInterface');
        $this->structureManager = $this->prophesize('Sulu\Component\Content\StructureManagerInterface');
        $this->contentType = $this->prophesize('Sulu\Component\Content\ContentTypeInterface');

        $this->structureResolver = new StructureResolver(
            $this->contentTypeManager->reveal(),
            $this->structureManager->reveal()
        );
    }

    public function testResolve()
    {
        $this->contentTypeManager->get('content_type')->willReturn($this->contentType);

        $this->contentType->getViewData(Argument::any())->willReturn('view');
        $this->contentType->getContentData(Argument::any())->willReturn('content');

        $excerptExtension = $this->prophesize('Sulu\Component\Content\StructureExtension\StructureExtension');
        $excerptExtension->getContentData(array('test1' => 'test1'))->willReturn(array('test1' => 'test1'));
        $this->structureManager->getExtension('test', 'excerpt')->willReturn($excerptExtension);

        $property = $this->prophesize('Sulu\Component\Content\PropertyInterface');
        $property->getName()->willReturn('property');
        $property->getContentTypeName()->willReturn('content_type');

        $structure = $this->prophesize('Sulu\Component\Content\Structure\Page');
        $structure->getKey()->willReturn('test');
        $structure->getExt()->willReturn(array('excerpt' => array('test1' => 'test1')));
        $structure->getUuid()->willReturn('some-uuid');
        $structure->getProperties(true)->willReturn(array($property->reveal()));
        $structure->getCreator()->willReturn(1);
        $structure->getChanger()->willReturn(1);
        $structure->getCreated()->willReturn('date');
        $structure->getChanged()->willReturn('date');

        $expected = array(
            'extension' => array(
                'excerpt' => array('test1' => 'test1')
            ),
            'uuid' => 'some-uuid',
            'view' => array(
                'property' => 'view'
            ),
            'content' => array(
                'property' => 'content'
            ),
            'creator' => 1,
            'changer' => 1,
            'created' => 'date',
            'changed' => 'date',
        );

        $this->assertEquals($expected, $this->structureResolver->resolve($structure->reveal()));
    }
}