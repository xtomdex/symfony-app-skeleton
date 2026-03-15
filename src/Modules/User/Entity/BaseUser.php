<?php

declare(strict_types=1);

namespace App\Modules\User\Entity;

use App\Domain\Behavior\Timestampable\TimestampableTrait;
use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Trait\EventsTrait;
use App\Domain\Persistence\Contract\EntityInterface;
use App\Modules\User\Enum\UserRole;
use App\Modules\User\Enum\UserStatus;
use App\Modules\User\Enum\UserType;
use App\Modules\User\Event\UserCreatedEvent;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
class BaseUser implements EntityInterface, AggregateRoot
{
    use TimestampableTrait, EventsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    protected ?string $id = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    protected string $username;

    #[ORM\Column(type: 'string')]
    protected string $password;

    #[ORM\Column(type: 'json')]
    protected array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string', length: 50, enumType: UserType::class)]
    private UserType $type;

    #[ORM\Column(type: 'string', length: 50, enumType: UserStatus::class)]
    private UserStatus $status;

    protected function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function create(string $id, string $username, string $password): static
    {
        $self = new static($id);
        $self->username = $username;
        $self->password = $password;
        $self->type = UserType::CLIENT;
        $self->status = UserStatus::ACTIVE;

        $self->recordEvent(new UserCreatedEvent($id, $username));

        return $self;
    }

    public static function createRoot(string $id, string $username, string $password): static
    {
        $self = static::create($id, $username, $password);
        $self->type = UserType::ROOT;
        $self->roles[] = UserRole::SUPER_ADMIN->value;

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getType(): UserType
    {
        return $this->type;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }
}
