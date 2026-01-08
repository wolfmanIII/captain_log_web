# Tooltip & micro-copy guidelines

## Obiettivo

Standardizzare tooltip e micro-copy associati ai bottoni azione principali (pulsanti, icone in tabelle, badge interattivi e collegamenti di navigation) per mantenere tono sci-fi, ridurre ripetizioni e facilitare futuri refactor.

## Pattern proposto

1. **Icona + tooltip preconfigurati**
   - Creare un macro Twig (es. `templates/_tooltip.html.twig`) che accetta `label`, `content`, `icon` e `dim`, e genera:
     ```twig
     <div class="tooltip tooltip-bottom" data-tip="{{ content }}">
         <button type="button" class="btn btn-xs btn-ghost" aria-label="{{ label }}">
             {{ include(icon, { dim: dim|default(20) }) }}
         </button>
     </div>
     ```
   - Permette di limitare HTML duplicato e uniformare posizione/colori.

2. **Linee guida copy**
   - Frasi brevi, verbo al presente: "View crew", "Adjust session calendar", "Copy ship code".
   - Indicazione di esclusivi vantaggi: "Launch mortgage PDF (draft)", "Track campaign budget".

3. **Zero copy ripetuto**
   - I tooltip non devono riportare lo stesso testo dei badge, ma ampliano il contesto (es. badge "Ops Guide" + tooltip "Open guided checklist").

4. **Badge + tooltip**
   - I badge esplicativi devono avere un tooltip solo quando sono cliccabili; usiamo la stessa macro per badge e bottoni con `class="badge"` e `data-tip`.

5. **Delega futura**
   - Includere il macro nei template chiave: sidebar, tabelle ship/crew/mortgage e homepage.
   - Un futuro refactor può anche estrarre il macro in un componente Stimulus per tooltip dinamici (timeout, aria-live, ecc.).

Torniamo domani: posso partire con il macro se lo confermi. Fammi sapere qualsiasi dettaglio extra prima di chiudere per la notte.
