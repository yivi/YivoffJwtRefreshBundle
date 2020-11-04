<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Test\resources;


use Yivoff\Bundle\JwtRefresh\Contracts\EncoderInterface;
use Yivoff\Bundle\JwtRefresh\Contracts\IdGeneratorInterface;
use Yivoff\Bundle\JwtRefresh\EventListener\AttachRefreshToken;
use Yivoff\Bundle\JwtRefresh\Security\Authenticator;

class Autowired
{

    private EncoderInterface $encoder;
    private IdGeneratorInterface $generator;
    private Authenticator $authenticator;
    private AttachRefreshToken $eventListener;

    public function __construct(
        EncoderInterface $encoder,
        IdGeneratorInterface $generator,
        Authenticator $authenticator,
        AttachRefreshToken $eventListener
    ) {
        $this->encoder       = $encoder;
        $this->generator     = $generator;
        $this->authenticator = $authenticator;
        $this->eventListener = $eventListener;
    }

    public function getEncoder(): EncoderInterface
    {
        return $this->encoder;
    }

    public function getGenerator(): IdGeneratorInterface
    {
        return $this->generator;
    }

    public function getAuthenticator(): Authenticator
    {
        return $this->authenticator;
    }

    public function getEventListener(): AttachRefreshToken
    {
        return $this->eventListener;
    }


}
