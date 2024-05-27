# Plugin WoldPress ContactForm7 - Dolibarr Sync

## Configuration générale

Un lien est présent dans le menu principal pour définir les options par défaut :
  - URL API
  - clé API
  - ID de l'utilisateur pour la création d'événements
  - le code action pour l'événement crée
  
 ## Configuration pour chaque formulaire
 
 Le plugin ajoute un onglet « Dolibarr sync » dans l'édition de chaque formulaire :
  - URL API (rempli avec les valeurs par défaut)
  - clé API (rempli avec les valeurs par défaut)
  - ID de la catégorie à laquelle lier les tiers crées par le plugin
  - Nom du tag formulaire pour le champ 'societe' (nom du tiers)
  - Nom du tag formulaire pour le champ 'siren'
  - Nom du tag formulaire pour le champ 'email'
  - Nom du tag formulaire pour le champ 'lastname' (nom du contact)
  - Nom du tag formulaire pour le champ 'fistname' (prénom du contact)
  
  ## Utilisation
  
  Lors de l'envoi du formulaire le plugin cherche un tiers correspondant à l'email utilisé. 
  Si le tiers est inexistant, un nouveau sera crée avec un contact associé. Ce tiers sera ajouté à la catégorie spécifiée.
  Puis un événement est crée et reprend en note le message envoyé par l'internaute.
  
  