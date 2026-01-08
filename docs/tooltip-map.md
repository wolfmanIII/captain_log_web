# Tooltip map

Il documento mappa i tooltip attuali ({{ data-tip }}) presenti nelle principali interfacce CRUD e propone copy coerenti con il tono sci-fi e le linee guida di `docs/tooltip-guidelines.md`. Lo scopo è avere una singola fonte di riferimento per creare poi il macro `_tooltip.html.twig` e standardizzare i micro-copy.

| Contesto | Tooltip attuale | Copy sci-fi suggerito | Note |
|---|---|---|---|
| `templates/ship/index.html.twig` – pulsante “Add ship” | `Add ship` | `Launch a new hull` | Tono action, chiaro su contesto navale. |
| `templates/ship/index.html.twig` – edit | `Edit ship` | `Tune ship manifest` | Richiama il controllo di registro. |
| `templates/ship/index.html.twig` – delete | `Delete ship` | `Decommission hull` | Sci-fi, coerente con “shipyard”. |
| `templates/ship/crew_select.html.twig` – roles modal | `Update the crew member's ship roles` | `Adjust bridge roles` | Copy più sintetico. |
| `templates/ship/crew_select.html.twig` – remove crew | `Remove this crew member from the ship` | `Unassign crewman from hull` | Mantiene indicazione di action. |
| `templates/ship/crew_select.html.twig` – search | `Filter crew by name or nickname` | `Search crew roster (name/nickname)` | Leggero accento operazione. |
| `templates/ship/crew_select.html.twig` – reset | `Clear filters and show all crew` | `Reset roster filters` | Più conciso. |
| `templates/ship/crew_select.html.twig` – add selected crew | `Assign the checked crew to this ship` | `Deploy selected crew aboard` | Modo narrativo per “assign”. |
| `templates/campaign/index.html.twig` – add | `Add campaign` | `Create a new session` | Tono operativo "mission". |
| `templates/campaign/index.html.twig` – details | `Campaign details` | `Review campaign clock` | Richiama focus campagna. |
| `templates/campaign/index.html.twig` – edit | `Edit campaign` | `Refine session agenda` | Coerente con gestione tempo. |
| `templates/campaign/index.html.twig` – delete | `Delete campaign` | `Retire campaign` | Alternativa sci-fi a “delete”. |
| `templates/mortgage/index.html.twig` – add | `Add mortgage` | `Record new financing` | Tono narrativo. |
| `templates/mortgage/index.html.twig` – edit | `Edit mortgage` | `Recalibrate financing` | Tono tecnico. |
| `templates/mortgage/index.html.twig` – delete | `Delete mortgage` | `Cancel mortgage` | Frase semplice. |
| `templates/company/index.html.twig` – add | `Add company` | `Register counterparty` | Indica scopo contrattuale. |
| `templates/company/index.html.twig` – edit | `Edit company` | `Update counterparty` | Consistente. |
| `templates/company/index.html.twig` – delete | `Delete company` | `Retire counterparty` | Tono narrativo. |
| `templates/cost/index.html.twig` – add | `Add cost` | `Log a new cost` | Coerente con ledger. |
| `templates/cost/index.html.twig` – edit | `Edit cost` | `Adjust cost entry` | Meno generico. |
| `templates/cost/index.html.twig` – delete | `Delete cost` | `Void cost entry` | Sci-fi leggermente burocratica. |
| `templates/income/index.html.twig` – add | `Add income` | `Log incoming credits` | Emphasizes credit flow. |
| `templates/income/index.html.twig` – edit | `Edit income` | `Revise income log` | Technical. |
| `templates/income/index.html.twig` – delete | `Delete income` | `Void income record` | Analogous to ledger adjustments. |
| `templates/annual_budget/index.html.twig` – add | `Add annual budget` | `Open new year ledger` | Narrativa. |
| `templates/annual_budget/index.html.twig` – view chart | `View chart` | `Inspect budget timeline` | Emphasize timeline. |
| `templates/annual_budget/index.html.twig` – edit | `Edit annual budget` | `Tune year ledger` | Sci-fi. |
| `templates/annual_budget/index.html.twig` – delete | `Delete annual budget` | `Retire year ledger` | Consistent. |
| `templates/crew/index.html.twig` – add | `Add crew member` | `Recruit crew member` | Theme-fitting. |
| `templates/crew/index.html.twig` – edit | `Edit crew member` | `Update crew profile` | Clear. |
| `templates/crew/index.html.twig` – delete | `Delete crew member` | `Release crew member` | Sci-fi field. |
| `templates/cost/index.html.twig` – add `Search crew` (if any) | — | `Search crew roster` | not present yet. |

Ogni voce dovrà poi essere trasferita nel nuovo macro `_tooltip.html.twig`, mantenendo il copy proposto. La tabella può essere estesa aggiungendo nuove interfacce se emergono altri tooltip.
