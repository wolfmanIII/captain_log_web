# Traveller Imperial Calendar (Day Ranges per Month)

Assuming `Holiday = 001` and sits outside any month, the day ranges for months 1–13 are:

- **Month 1:** 002–029  
- **Month 2:** 030–057  
- **Month 3:** 058–085  
- **Month 4:** 086–113  
- **Month 5:** 114–141  
- **Month 6:** 142–169  
- **Month 7:** 170–197  
- **Month 8:** 198–225  
- **Month 9:** 226–253  
- **Month 10:** 254–281  
- **Month 11:** 282–309  
- **Month 12:** 310–337  
- **Month 13:** 338–365  

These ranges reflect a 365‑day year with one interstitial holiday (001) before Month 1.

## Implementation notes (proposta)

- **Datatype**: `App\Model\ImperialDate` (`year`, `day` where `day` è 1–365 con 1 = Holiday).  
- **Form Type**: `App\Form\Type\ImperialDateType` (campi Year, Month, Day-in-month con calcolo automatico del day-of-year). Opzioni: `min_year`, `max_year` (default 1105–9999).  
- **Stimulus datepicker**: controller `imperial-date` (asset) popola i giorni validi per il mese Traveller e aggiorna il `day` assoluto.  
- **Usage**: nel form `->add('imperialDate', ImperialDateType::class, [...])`; il campo `day` è quello salvato, mentre month/day vengono usati solo per l’UX.  
- **Range mapping**: i range di questo file sono codificati sia nel FormType sia nel controller JS per tenere allineata la UX con la validazione server.
