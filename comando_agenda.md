```
  Preencher o bloco inteiro (19→23) de uma vez:
  for h in 19 20 21 22 23; do
    curl -s -X POST "https://curso.chr.eti.br/ajax/matricula20.php?recurso=agenda" \
      -d "dia=Sex" -d "hora=${h}:00" -d "texto=Aula de AJAX" ; echo
  done
```
