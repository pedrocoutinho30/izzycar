<?php

namespace Tests\Fluxo\Support;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Transporte de email que FALHA sempre (simula SMTP em baixo). Nada sai da máquina.
 * A mensagem inclui os destinatários, como acontece nos erros reais de SMTP
 * ("550 <endereco> User unknown"), para se poder ver se o log guarda dados pessoais.
 */
class TransportFalha extends AbstractTransport
{
    public static int $tentativas = 0;

    protected function doSend(SentMessage $message): void
    {
        self::$tentativas++;
        $para = collect($message->getEnvelope()->getRecipients())->map->getAddress()->implode(',');

        throw new TransportException("Simulado: SMTP indisponível (550 <{$para}> rejeitado)");
    }

    public function __toString(): string
    {
        return 'falha://simulado';
    }
}
