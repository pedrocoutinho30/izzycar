<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Angariação — Izzycar</title>
    @include('pdf._contract-styles')
</head>

@php
    // Dados em falta ficam em linha para preencher à mão.
    $linha = fn ($valor) => filled($valor) ? e($valor) : '____________________';
@endphp

<body>

    <header>
        @if($logo)
        <img src="{{ $logo }}" alt="Izzycar">
        @endif
        <div class="ref">
            Contrato de Angariação<br>
            Emitido em {{ $dataEmissao }}
        </div>
    </header>

    <footer>
        Izzycar · NIF {{ $prestador['nif'] }}
        @if(!empty($prestador['telefone'])) · Tel. {{ $prestador['telefone'] }}@endif
        @if(!empty($prestador['email'])) · {{ $prestador['email'] }}@endif
        — Página <span class="pagenum"></span>
    </footer>

    <h1>CONTRATO DE ANGARIAÇÃO</h1>
    <div class="subtitulo">Programa de angariadores Izzycar</div>

    <h2>1. Identificação das Partes</h2>
    <table class="partes" width="100%" cellspacing="6">
        <tr>
            <td>
                <div class="titulo">Izzycar (Prestador)</div>
                <b>{{ $prestador['nome'] }}</b><br>
                NIF: {{ $prestador['nif'] }}<br>
                Morada: {{ $prestador['morada'] }}<br>
                @if(!empty($prestador['telefone']))Telefone: {{ $prestador['telefone'] }}<br>@endif
                @if(!empty($prestador['email']))Email: {{ $prestador['email'] }}@endif
            </td>
            <td>
                <div class="titulo">Angariador</div>
                <b>{{ $angariador['nome'] }}</b><br>
                NIF: {!! $linha($angariador['nif']) !!}<br>
                Morada: {!! $linha($angariador['morada']) !!}<br>
                @if(!empty($angariador['telefone']))Telefone: {{ $angariador['telefone'] }}<br>@endif
                @if(!empty($angariador['email']))Email: {{ $angariador['email'] }}<br>@endif
                IBAN: {!! $linha($angariador['iban']) !!}
            </td>
        </tr>
    </table>

    <h2>2. Objeto do Contrato</h2>
    <p>
        O presente contrato tem por objeto a angariação, pelo Angariador, de potenciais clientes para o serviço de
        importação, legalização e entrega de veículos prestado pela Izzycar, através do link e do código pessoais que
        esta lhe atribui, mediante o pagamento da comissão prevista na secção 6.
    </p>

    <h2>3. Natureza da Relação</h2>
    <ul>
        <li>O Angariador atua como prestador de serviços autónomo, sem qualquer vínculo laboral com a Izzycar, sem horário nem obrigação de resultados, e sem exclusividade para nenhuma das Partes.</li>
        <li>O Angariador não tem poderes para representar a Izzycar, celebrar contratos, assumir compromissos em seu nome nem receber pagamentos dos clientes.</li>
        <li>Nos contactos com potenciais clientes, o Angariador apresenta-se sempre como <b>angariador independente da Izzycar</b>, nunca como seu colaborador, funcionário ou representante.</li>
    </ul>

    <h2>4. Obrigações do Angariador</h2>
    <ul>
        <li>Enviar as leads sempre através do seu link ou código pessoal, único meio de as associar a si.</li>
        <li>Prestar aos potenciais clientes informação verdadeira sobre o serviço, sem prometer preços, prazos ou condições diferentes dos que constam da cotação emitida pela Izzycar.</li>
        <li>Não praticar atos que prejudiquem a imagem ou o bom nome da Izzycar.</li>
        <li>Cumprir a legislação aplicável à sua atividade, incluindo as obrigações fiscais associadas à comissão que recebe.</li>
        <li>Respeitar as regras sobre o uso da marca, a prospeção e as comunicações comerciais, e a responsabilidade, previstas nas secções 10, 11 e 12.</li>
    </ul>

    <h2>5. Obrigações da Izzycar</h2>
    <ul>
        <li>Disponibilizar ao Angariador o link e o código pessoais e a plataforma de acompanhamento das suas leads e comissões.</li>
        <li>Informar o Angariador do estado das leads que lhe estão associadas.</li>
        <li>Pagar a comissão nos termos da secção 6.</li>
    </ul>

    <h2>6. Comissão</h2>
    <div class="pagamento">
        <b>Comissão por cotação convertida</b>
        <ul>
            <li>Valor: <b>{!! $comissao ? e($comissao) : '____________' !!}</b> por cada cotação convertida atribuída ao Angariador.</li>
            <li>É devida <b>após a entrega da viatura ao cliente</b> e paga <b>até {{ $prazoPagamentoHoras }} horas depois</b>, por transferência bancária para o IBAN indicado na secção 1.</li>
        </ul>
    </div>
    <ul>
        <li>A comissão só é devida se a viatura for entregue ao cliente. Se o cliente cancelar o serviço ou a entrega não se realizar, não há lugar a comissão.</li>
        <li>O valor da comissão fica fixado no momento em que o Angariador é associado à cotação, e uma alteração posterior do valor aplica-se apenas às cotações seguintes.</li>
        <li>Quando legalmente aplicável, o Angariador emite o respetivo recibo ou fatura.</li>
        <li>Não conferem direito a comissão as leads obtidas através de comunicações ou meios que violem as secções 10 e 11.</li>
    </ul>

    <h2>7. Atribuição de Leads</h2>
    <p>
        Só se consideram angariadas pelo Angariador as leads que cheguem à Izzycar com o seu código pessoal. Se um
        potencial cliente já estiver associado a um angariador, prevalece a primeira associação.
    </p>

    <h2>8. Confidencialidade e Proteção de Dados</h2>
    <p>
        O Angariador mantém a confidencialidade das informações da Izzycar e dos dados pessoais a que aceda no âmbito
        deste contrato, usando-os exclusivamente para os fins nele previstos e em cumprimento do Regulamento Geral
        sobre a Proteção de Dados. Os dados pessoais do Angariador são tratados pela Izzycar para a execução do
        contrato e cumprimento de obrigações legais, podendo o Angariador exercer os seus direitos através do
        contacto da Izzycar indicado neste contrato.
    </p>

    <h2>9. Duração e Cessação</h2>
    <ul>
        <li>O contrato é celebrado por tempo indeterminado.</li>
        <li>Qualquer das Partes pode fazê-lo cessar, por comunicação escrita à outra com a antecedência mínima de {{ $preAvisoDias }} dias.</li>
        <li>Qualquer das Partes pode resolvê-lo em caso de incumprimento grave da outra, após aviso por escrito e prazo razoável para o corrigir.</li>
        <li>O incumprimento das secções 10, 11 ou 12 constitui incumprimento grave e permite à Izzycar resolver o contrato de imediato, por comunicação escrita, sem necessidade do aviso prévio nem do prazo de correção previstos nesta secção.</li>
        <li>Cessando o contrato por qualquer motivo, o Angariador deixa de imediato de usar o nome Izzycar e elimina as contas, materiais e campanhas que lhe façam referência.</li>
        <li>A cessação não afeta as comissões já devidas nos termos da secção 6, com exceção das excluídas pela própria secção 6.</li>
    </ul>

    <h2>10. Uso do Nome, Marca e Domínio Izzycar</h2>
    <ul>
        <li>O Angariador não pode usar o nome Izzycar, o logótipo, a imagem gráfica ou o domínio izzycar.pt sem autorização prévia e escrita da Izzycar.</li>
        <li>Sem essa autorização, o Angariador não pode criar contas, perfis, páginas, endereços de email, nomes de remetente ou campanhas que contenham o nome ou o domínio Izzycar, em qualquer plataforma.</li>
        <li>Nos contactos com potenciais clientes, aplica-se o disposto na secção 3: o Angariador apresenta-se sempre como angariador independente da Izzycar.</li>
        <li>As contas, perfis e configurações já existentes à data da assinatura que contrariem esta secção são removidos ou alterados no prazo de 3 dias, salvo autorização escrita da Izzycar, e o Angariador confirma-o por escrito.</li>
    </ul>

    <h2>11. Prospeção e Comunicações Comerciais</h2>
    <ul>
        <li>É proibido o envio de comunicações em massa ou automatizadas que mencionem a Izzycar ou os seus serviços, por qualquer meio (email, SMS, LinkedIn ou outras redes sociais, mensagens de voz ou sequências automáticas), sem aprovação prévia e escrita da Izzycar quanto ao conteúdo, ao canal e ao público-alvo.</li>
        <li>Qualquer texto, áudio, imagem ou outro material que refira a Izzycar deve ser aprovado pela Izzycar antes de ser utilizado.</li>
        <li>O Angariador não envia comunicações comerciais não solicitadas a pessoas singulares sem o seu consentimento prévio e expresso, nos termos do Regulamento Geral sobre a Proteção de Dados (RGPD) e da Lei n.º 41/2004, de 18 de agosto.</li>
        <li>O Angariador nunca envia comunicações a partir do domínio izzycar.pt nem de endereços que se façam passar pela Izzycar.</li>
    </ul>

    <h2>12. Responsabilidade</h2>
    <p>
        O Angariador é o único responsável pelos dados pessoais que recolhe e pelas comunicações que envia no âmbito da
        sua atividade, incluindo o cumprimento do RGPD e da legislação sobre comunicações eletrónicas. O Angariador
        responde perante a Izzycar por quaisquer coimas, indemnizações, custos ou danos, incluindo danos à imagem e
        reputação, que resultem do incumprimento deste contrato ou da legislação aplicável.
    </p>

    <h2>13. Despesas</h2>
    <div class="pagamento">
        <b>Despesas da atividade de angariação</b>
        <ul>
            <li>Todas as despesas do Angariador (ferramentas, subscrições, campanhas, publicidade, deslocações ou outras) são <b>suportadas exclusivamente por si</b> e <b>não são reembolsadas</b> pela Izzycar.</li>
            <li>A <b>única remuneração</b> do Angariador é a comissão por cotação convertida prevista na secção 6. Não há lugar a adiantamentos, ajudas de custo ou outros pagamentos.</li>
        </ul>
    </div>

    <h2>14. Lei Aplicável e Resolução de Litígios</h2>
    <p>
        O contrato rege-se pela lei portuguesa. Para a resolução de litígios é competente o tribunal da comarca do
        domicílio do Angariador, sem prejuízo do recurso a entidades de resolução alternativa de litígios.
    </p>

    <h2>15. Assinaturas</h2>
    <table class="assinaturas" width="100%">
        <tr>
            <td>
                <div style="height: 55px;"></div>
                _________________________________ <br>
                Assinatura do Angariador <br>
                Data: ____ / ____ / ______
            </td>
            <td>
                <div style="height: 55px; text-align: center;">
                    @if($signaturePath)
                    <img src="{{ $signaturePath }}" alt="Assinatura" style="height: 50px;">
                    @endif
                </div>
                _________________________________ <br>
                Assinatura da Izzycar <br>
                Data: {{ $dataEmissao }} <br>
            </td>
        </tr>
    </table>

</body>

</html>
