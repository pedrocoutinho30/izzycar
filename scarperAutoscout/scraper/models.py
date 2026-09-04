from dataclasses import dataclass, field
from typing import Optional


@dataclass
class SearchSpec:
    name: str
    make: str
    model: Optional[str] = None
    filters: dict = field(default_factory=dict)


@dataclass
class Listing:
    external_id: str
    url: str
    source: str = "autoscout24"
    make: Optional[str] = None
    model: Optional[str] = None
    version: Optional[str] = None
    first_registration_year: Optional[int] = None
    mileage_km: Optional[int] = None
    power_hp: Optional[int] = None
    fuel: Optional[str] = None
    gearbox: Optional[str] = None
    body_type: Optional[str] = None
    seller_type: Optional[str] = None
    seller_name: Optional[str] = None
    seller_phone: Optional[str] = None
    location_zip: Optional[str] = None
    location_city: Optional[str] = None
    price_eur: Optional[int] = None
