<?php

namespace App\Json;

class JsonResponse {

    private string $co2;

    private string $rh;

    private string $t;

    private string $device;

    /**
     * @return string
     */
    public function getCo2(): string {
        return $this->co2;
    }

    /**
     * @param string $co2
     */
    public function setCo2(string $co2): void {
        $this->co2 = $co2;
    }

    /**
     * @return string
     */
    public function getRh(): string {
        return $this->rh;
    }

    /**
     * @param string $rh
     */
    public function setRh(string $rh): void {
        $this->rh = $rh;
    }

    /**
     * @return string
     */
    public function getT(): string {
        return $this->t;
    }

    /**
     * @param string $t
     */
    public function setT(string $t): void {
        $this->t = $t;
    }

    /**
     * @return string
     */
    public function getDevice(): string {
        return $this->device;
    }

    /**
     * @param string $device
     */
    public function setDevice(string $device): void {
        $this->device = $device;
    }
}