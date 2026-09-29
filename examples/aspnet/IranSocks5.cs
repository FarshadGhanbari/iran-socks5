// ASP.NET / C# via SOCKS5
// NuGet: MihaZupan.HttpToSocks5Proxy
//
// dotnet add package MihaZupan.HttpToSocks5Proxy

using System;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using MihaZupan;

public static class IranSocks5
{
    public static HttpClient CreateClient()
    {
        var host = Environment.GetEnvironmentVariable("IRAN_SOCKS5_HOST")
            ?? throw new InvalidOperationException("Set IRAN_SOCKS5_HOST");
        var port = int.Parse(Environment.GetEnvironmentVariable("IRAN_SOCKS5_PORT") ?? "1080");
        var user = Environment.GetEnvironmentVariable("IRAN_SOCKS5_USER")
            ?? throw new InvalidOperationException("Set IRAN_SOCKS5_USER");
        var pass = Environment.GetEnvironmentVariable("IRAN_SOCKS5_PASS")
            ?? throw new InvalidOperationException("Set IRAN_SOCKS5_PASS");

        var proxy = new HttpToSocks5Proxy(host, port, user, pass);
        var handler = new HttpClientHandler { Proxy = proxy };
        return new HttpClient(handler, disposeHandler: true)
        {
            Timeout = TimeSpan.FromSeconds(30),
        };
    }

    public static async Task<string> GetStringAsync(string url)
    {
        using var client = CreateClient();
        return await client.GetStringAsync(url);
    }

    public static async Task<string> PostJsonAsync(string url, object payload)
    {
        using var client = CreateClient();
        var json = JsonSerializer.Serialize(payload);
        using var content = new StringContent(json, Encoding.UTF8, "application/json");
        using var response = await client.PostAsync(url, content);
        response.EnsureSuccessStatusCode();
        return await response.Content.ReadAsStringAsync();
    }
}

// Demo:
// Console.WriteLine(await IranSocks5.GetStringAsync("https://ipinfo.io/json"));
