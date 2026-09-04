"""Tradução alemão -> português dos termos de equipamento da AutoScout24.

A Standvirtual já devolve o "label" em português (ver equipment_client.py), por
isso só a AutoScout24 precisa de tradução aqui. A AutoScout24 usa um vocabulário
fixo (não é texto livre) - as chaves abaixo foram confirmadas contra anúncios
reais (ver conversa/commits desta funcionalidade). Um termo alemão que apareça e
não esteja neste dicionário fica em alemão até alguém o traduzir manualmente na
página de Equipamento (não bloqueia nada, só fica menos bonito).
"""
import re

AUTOSCOUT_DE_PT = {
    # Conforto
    "3-Zonen-Klimaautomatik": "Ar condicionado automático (3 zonas)",
    "Klimaautomatik": "Ar condicionado automático",
    "Klimaanlage": "Ar condicionado",
    "Armlehne": "Apoio de braço",
    "Berganfahrassistent": "Assistente de arranque em subida",
    "Einparkhilfe": "Sensores de estacionamento",
    "Einparkhilfe Rückfahrkamera": "Câmara de marcha-atrás",
    "Einparkhilfe Sensoren hinten": "Sensores de estacionamento traseiros",
    "Einparkhilfe Sensoren vorne": "Sensores de estacionamento dianteiros",
    "Elektrische Fensterheber": "Vidros elétricos",
    "Elektrische Heckklappe": "Porta-bagagens elétrico",
    "Elektrische Seitenspiegel": "Espelhos retrovisores elétricos",
    "Elektrische Sitze": "Bancos elétricos",
    "Getönte Scheiben": "Vidros fumados",
    "Lederlenkrad": "Volante em pele",
    "Lichtsensor": "Sensor de luz",
    "Lordosenstütze": "Apoio lombar",
    "Massagesitze": "Bancos com massagem",
    "Multifunktionslenkrad": "Volante multifunções",
    "Navigationssystem": "Sistema de navegação",
    "Regensensor": "Sensor de chuva",
    "Schlüssellose Zentralverriegelung": "Fecho central sem chave (keyless)",
    "Sitzheizung": "Bancos aquecidos",
    "Start/Stop-Automatik": "Sistema Start/Stop",
    "Tempomat": "Piloto automático (cruise control)",
    "Umklappbarer Beifahrersitz": "Banco do passageiro rebatível",
    "Elektronische Parkbremse": "Travão de mão eletrónico",
    "Panoramadach": "Teto panorâmico",
    "Glasschiebedach": "Teto de abrir em vidro",
    "Schiebedach": "Teto de abrir",
    "Sitzbelüftung": "Bancos ventilados",
    "Standheizung": "Aquecimento independente do motor",
    "Zweizonen-Klimaautomatik": "Ar condicionado automático (2 zonas)",
    "2-Zonen-Klimaautomatik": "Ar condicionado automático (2 zonas)",
    "360° Kamera": "Câmara 360°",
    "Allwetterreifen": "Pneus 4 estações",
    "teilb. Rücksitzbank": "Banco traseiro rebatível (parcial)",

    # Multimédia / entretenimento
    "Android Auto": "Android Auto",
    "Apple CarPlay": "Apple CarPlay",
    "Bluetooth": "Bluetooth",
    "Bordcomputer": "Computador de bordo",
    "DAB-Radio": "Rádio digital (DAB)",
    "Freisprecheinrichtung": "Sistema mãos-livres",
    "Induktionsladen für Smartphones": "Carregador de indução para telemóvel",
    "MP3": "Leitor MP3",
    "Musikstreaming integriert": "Streaming de música integrado",
    "Radio": "Rádio",
    "USB": "Entrada USB",
    "W-Lan / Wifi Hotspot": "Wi-Fi / Hotspot",
    "Touchscreen": "Ecrã tátil",
    "Sprachsteuerung": "Comando por voz",
    "Radio": "Rádio",
    "TV": "TV",
    "Soundsystem": "Sistema de som",
    "Volldigitales Kombiinstrument": "Quadro de instrumentos digital",
    "Head-up display": "Head-up display",

    # Extras / exterior
    "Alufelgen": "Jantes de liga",
    "Alarmanlage": "Alarme",
    "CD": "Leitor de CD",
    "Zentralverriegelung": "Fecho central",
    "Spurhalteassistent": "Assistente de mudança de faixa",
    "Totwinkel-Assistent": "Assistente de ângulo morto",
    "Ambientebeleuchtung": "Iluminação ambiente",
    "Anhängerkupplung": "Engate de reboque",
    "Dachreling": "Barras de tejadilho",
    "Gepäckraumabtrennung": "Separador de bagageira",
    "Innenspiegel automatisch abblendend": "Espelho retrovisor com anti-encandeamento automático",
    "Katalysator": "Catalisador",
    "Pannenkit": "Kit de reparação de furos",
    "Partikelfilter": "Filtro de partículas",
    "Reichweitenverlängerer": "Extensor de autonomia",
    "Schaltwippen": "Comandos de mudanças no volante",
    "Sommerreifen": "Pneus de verão",
    "Winterreifen": "Pneus de inverno",
    "Winterpaket": "Pack inverno",
    "Spoiler": "Spoiler",
    "Sportfahrwerk": "Suspensão desportiva",
    "Sportpaket": "Pack desportivo",
    "Sportsitze": "Bancos desportivos",
    "Xenon-Scheinwerfer": "Faróis de xénon",
    "Bi-Xenon Scheinwerfer": "Faróis Bi-Xénon",
    "Xenonscheinwerfer": "Faróis de xénon",
    "Stahlfelgen": "Jantes de aço",
    "E10-geeignet": "Compatível com E10",
    "Beheizbares Lenkrad": "Volante aquecido",
    "Beheizbare Frontscheibe": "Para-brisas aquecido",
    "Reserverad": "Roda sobresselente",
    "Lederausstattung": "Estofos em pele",
    "Einparkhilfe selbstlenkendes System": "Assistente de estacionamento automático",

    # Segurança / assistência à condução
    "ABS": "ABS",
    "Abstandstempomat": "Piloto automático adaptativo",
    "Abstandswarner": "Aviso de distância de segurança",
    "Airbag hinten": "Airbag traseiro",
    "Beifahrerairbag": "Airbag do passageiro",
    "Blendfreies Fernlicht": "Máximos automáticos sem encandear",
    "ESP": "ESP (controlo de estabilidade)",
    "Fahrerairbag": "Airbag do condutor",
    "Fernlichtassistent": "Assistente de máximos",
    "Geschwindigkeits-begrenzungsanlage": "Limitador de velocidade",
    "Isofix": "Isofix",
    "Kopfairbag": "Airbag de cabeça (cortina)",
    "Kurvenlicht": "Faróis direcionais (curva)",
    "LED-Scheinwerfer": "Faróis LED",
    "LED-Tagfahrlicht": "Luzes diurnas LED",
    "Müdigkeitswarnsystem": "Aviso de fadiga do condutor",
    "Nebelscheinwerfer": "Faróis de nevoeiro",
    "Notbremsassistent": "Assistente de travagem de emergência",
    "Notrufsystem": "Sistema de chamada de emergência (eCall)",
    "Reifendruckkontrollsystem": "Sensor de pressão dos pneus",
    "Seitenairbag": "Airbag lateral",
    "Servolenkung": "Direção assistida",
    "Tagfahrlicht": "Luzes diurnas",
    "Traktionskontrolle": "Controlo de tração",
    "Verkehrszeichenerkennung": "Reconhecimento de sinais de trânsito",
    "Voll-LED Scheinwerfer": "Faróis Full LED",
    "Wegfahrsperre": "Imobilizador",
    "Zentralverriegelung mit Funkfernbedienung": "Fecho central com comando",
}


ALLOY_WHEELS_RE = re.compile(r'^Alufelgen \((\d+)"\)$')


def translate(source, text):
    if source != "autoscout24":
        return text
    if text in AUTOSCOUT_DE_PT:
        return AUTOSCOUT_DE_PT[text]

    # O tamanho da jante varia por carro ("Alufelgen (15\")", "(19\")", ...) - regra
    # à parte em vez de listar cada tamanho possível no dicionário.
    match = ALLOY_WHEELS_RE.match(text)
    if match:
        return 'Jantes de liga ({}")'.format(match.group(1))

    return text
