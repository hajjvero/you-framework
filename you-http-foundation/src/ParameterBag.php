<?php

namespace YouHttpFoundation;

/**
 * ParameterBag est un conteneur pour les paires clé/valeur.
 *
 * Cette classe est utilisée pour gérer les paramètres de requête ou de réponse.
 */
class ParameterBag
{
    /**
     * Stockage des paramètres.
     *
     * @var array
     */
    protected array $parameters;

    /**
     * Constructeur.
     *
     * @param array $parameters Un tableau de paramètres
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Retourne tous les paramètres.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Retourne les clés des paramètres.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Remplace les paramètres actuels par un nouveau jeu de paramètres.
     *
     * @param array $parameters Un tableau de paramètres
     */
    public function replace(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Ajoute des paramètres.
     *
     * @param array $parameters Un tableau de paramètres
     */
    public function add(array $parameters = []): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Récupère un paramètre par son nom.
     *
     * @param string $key     La clé
     * @param mixed  $default La valeur par défaut si le paramètre n'existe pas
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->parameters[$key] : $default;
    }

    /**
     * Définit un paramètre par son nom.
     *
     * @param string $key   La clé
     * @param mixed  $value La valeur
     */
    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Vérifie si un paramètre existe.
     *
     * @param string $key La clé
     *
     * @return bool Vrai si le paramètre existe, faux sinon
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Supprime un paramètre.
     *
     * @param string $key La clé
     */
    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }

    /**
     * Compte le nombre de paramètres.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->parameters);
    }
}
