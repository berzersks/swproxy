# Swoole Relay
## _Um servidor de retransmissão_

se você acessar http://127.0.0.1:9501 ele seria como http://example.com onde apenas GET/POST funcionam.

## Instalação

Para instalar é simples.
```sh
git clone https://github.com/berzersks/swproxy && cd swproxy
chmod -R 777 .
./install
```

Para Iniciar...
```sh
./swoola start JSONplaceholder 9500 https://jsonplaceholder.typicode.com no-secure
```
Isto inicia um servidor em http://localhost:9500 que espelha https://jsonplaceholder.typicode.com.


Caso deseje utilizar SSL:
```sh
./swoola start relayServer2 8800 https://jsonplaceholder.typicode.com auto-secure
    # Vá em /plugins/configInterface.json e modifique os caminhos dos certificados.
```



## Porque estou proíbido de usar em produção?

Este projeto foi apenas o resultado de várias conversas de dois amigos pela madrugada. 
Eu o **autor** sempre estou com a mente derramando café em neve, meu colega Kris[Parabellium] deve estar segurando uma garrafa de bebida.

**PORQUE VOCÊ UTILIZARIA ESTE PROJETO EM PRODUÇÃO?!**



__Ajude a comunidade Swoole: https://www.swoole.com__
